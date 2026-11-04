<?php

namespace App\Http\Controllers\Gestion\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarClienteRequest;
use App\Http\Requests\GuardarClienteRequest;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    public function index(Request $request): View
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        $filters = $this->filters($request);
        $consultaClientes = fn (): Builder => $this->consultaClientesEnAlcance($usuario, 'gestion.clientes.consultar');
        $consultaPedidos = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            Pedido::query(), $usuario, 'gestion.pedidos.consultar', 'usuario_id',
        );
        $customers = $this->customerQuery($filters, $usuario)
            ->with('user:id,nombre,correo,rol')
            ->orderBy('nombre_contacto')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $userIds = $customers->getCollection()->pluck('usuario_id')->filter()->values();
        $lastPurchases = $userIds->isEmpty()
            ? collect()
            : $consultaPedidos()
                ->whereIn('usuario_id', $userIds)
                ->latest('creado_en')
                ->latest('id')
                ->get(['id', 'usuario_id', 'total', 'creado_en'])
                ->groupBy('usuario_id')
                ->map(static fn (Collection $orders): ?Pedido => $orders->first());
        $totalPurchases = (float) $consultaPedidos()->sum('total');
        $lastPurchase = $consultaPedidos()->latest('creado_en')->latest('id')->first(['id', 'total', 'creado_en']);
        $recentOrders = $consultaPedidos()->with('user:id,nombre')->latest('creado_en')->latest('id')->limit(3)
            ->get(['id', 'usuario_id', 'total', 'creado_en']);
        $recentCustomers = $consultaClientes()->latest('creado_en')->limit(3)
            ->get(['id', 'nombre_contacto', 'correo', 'creado_en']);
        $recentActivity = $this->recentActivity($recentOrders, $recentCustomers);

        return view('gestion.clientes.listar', [
            'customers' => $customers,
            'filters' => $filters,
            'cities' => $consultaClientes()->whereNotNull('ciudad')->where('ciudad', '<>', '')->distinct()->orderBy('ciudad')->pluck('ciudad'),
            'totalCustomers' => $consultaClientes()->count(),
            'activeCustomers' => $consultaClientes()->where('activo', true)->count(),
            'wholesaleCustomers' => $consultaClientes()->where('tipo', 'mayorista')->count(),
            'retailCustomers' => $consultaClientes()->where('tipo', 'minorista')->count(),
            'activeAccounts' => $consultaClientes()->where('activo', true)->whereNotNull('usuario_id')->count(),
            'totalPurchases' => $totalPurchases,
            'lastPurchase' => $lastPurchase,
            'lastPurchases' => $lastPurchases,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function store(GuardarClienteRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        $customer = Cliente::create($request->validated() + [
            'activo' => true,
            'creado_en' => now(),
            ...$this->autorizacion->operationScopeAttributes($usuario, 'clientes'),
        ]);
        $audit->record($usuario->id, 'customer.created', 'clientes', $customer->id, null, $customer->only(['tipo', 'documento', 'nombre_contacto', 'correo']), $request->ip());

        return back()->with('status', 'Cliente registrado.');
    }

    public function update(ActualizarClienteRequest $request, Cliente $customer, RegistradorAuditoria $audit): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        $this->verificarAccesoCliente($customer, $usuario, 'gestion.clientes.actualizar');

        $before = $customer->only(['tipo', 'documento', 'nombre_contacto', 'correo', 'activo']);
        $customer->update($request->validated());
        $audit->record($usuario->id, 'customer.updated', 'clientes', $customer->id, $before, $customer->only(array_keys($before)), $request->ip());

        return back()->with('status', 'Cliente actualizado.');
    }

    public function deactivate(Request $request, Cliente $customer, RegistradorAuditoria $audit): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        $this->verificarAccesoCliente($customer, $usuario, 'gestion.clientes.eliminar');

        $customer->update(['activo' => false]);
        $audit->record($usuario->id, 'customer.deactivated', 'clientes', $customer->id, ['activo' => true], ['activo' => false], $request->ip());

        return back()->with('status', 'Cliente desactivado.');
    }

    /**
     * @param  array{buscar:?string,tipo:?string,estado:?string,ciudad:?string,por_pagina:int}  $filters
     * @return Builder<Cliente>
     */
    private function customerQuery(array $filters, User $usuario): Builder
    {
        return $this->consultaClientesEnAlcance($usuario, 'gestion.clientes.consultar')
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('nombre_contacto', 'like', '%'.$search.'%')
                        ->orWhere('razon_social', 'like', '%'.$search.'%')
                        ->orWhere('documento', 'like', '%'.$search.'%')
                        ->orWhere('correo', 'like', '%'.$search.'%')
                        ->orWhere('telefono', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['tipo'], fn (Builder $query, string $type): Builder => $query->where('tipo', $type))
            ->when($filters['estado'] === 'activo', fn (Builder $query): Builder => $query->where('activo', true))
            ->when($filters['estado'] === 'inactivo', fn (Builder $query): Builder => $query->where('activo', false))
            ->when($filters['ciudad'], fn (Builder $query, string $city): Builder => $query->where('ciudad', $city));
    }

    private function verificarAccesoCliente(Cliente $cliente, User $usuario, string $permiso): void
    {
        $visible = $this->consultaClientesEnAlcance($usuario, $permiso)
            ->whereKey($cliente->getKey())
            ->exists();

        abort_unless($visible, 404);
    }

    private function consultaClientesEnAlcance(User $usuario, string $permiso): Builder
    {
        if ($this->autorizacion->allowsGlobal($usuario, $permiso)) {
            return $this->autorizacion->scopeRecordsFor(Cliente::query(), $usuario, $permiso);
        }

        $ubicacion = $this->autorizacion->operationSite($usuario);
        abort_unless(
            $ubicacion['empresa_id'] !== null
                && $ubicacion['sede_id'] !== null
                && $this->autorizacion->allowsRecord($usuario, $permiso, $ubicacion['empresa_id'], $ubicacion['sede_id']),
            403,
        );

        return Cliente::query()->where('clientes.empresa_id', $ubicacion['empresa_id']);
    }

    /**
     * @return array{buscar:?string,tipo:?string,estado:?string,ciudad:?string,por_pagina:int}
     */
    private function filters(Request $request): array
    {
        $search = trim($request->string('buscar')->toString());
        $type = trim($request->string('tipo')->toString());
        $status = trim($request->string('estado')->toString());
        $city = trim($request->string('ciudad')->toString());
        $perPage = $request->integer('por_pagina', 10);

        return [
            'buscar' => $search !== '' ? $search : null,
            'tipo' => in_array($type, ['minorista', 'mayorista'], true) ? $type : null,
            'estado' => in_array($status, ['activo', 'inactivo'], true) ? $status : null,
            'ciudad' => $city !== '' ? $city : null,
            'por_pagina' => in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
        ];
    }

    /**
     * @param  Collection<int, Pedido>  $recentOrders
     * @param  Collection<int, Cliente>  $recentCustomers
     * @return Collection<int, array{icon:string,color:string,title:string,detail:string,date:mixed}>
     */
    private function recentActivity(Collection $recentOrders, Collection $recentCustomers): Collection
    {
        $activities = collect();

        foreach ($recentOrders as $order) {
            $activities->push([
                'icon' => 'cart3',
                'color' => 'green',
                'title' => 'Compra registrada',
                'detail' => ($order->user?->nombre ?: 'Cliente').' · S/ '.number_format((float) $order->total, 2),
                'date' => $order->creado_en,
            ]);
        }

        foreach ($recentCustomers as $customer) {
            $activities->push([
                'icon' => 'person-plus',
                'color' => 'blue',
                'title' => 'Cliente registrado',
                'detail' => $customer->nombre_contacto.' · '.$customer->correo,
                'date' => $customer->creado_en,
            ]);
        }

        return $activities->sortByDesc('date')->take(4)->values();
    }
}
