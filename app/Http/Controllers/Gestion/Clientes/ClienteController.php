<?php

namespace App\Http\Controllers\Gestion\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarClienteRequest;
use App\Http\Requests\GuardarClienteRequest;
use App\Http\Requests\ImportarClientesRequest;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase ClienteController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ClienteController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
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
        $totalCustomers = $consultaClientes()->count();
        $activeCustomers = $consultaClientes()->where('activo', true)->count();
        $wholesaleCustomers = $consultaClientes()->where('tipo', 'mayorista')->count();
        $retailCustomers = $consultaClientes()->where('tipo', 'minorista')->count();
        $activeAccounts = $consultaClientes()->where('activo', true)->whereNotNull('usuario_id')->count();
        $customerPresentation = $customers->getCollection()->mapWithKeys(function (Cliente $customer) use ($lastPurchases): array {
            $lastPurchaseForCustomer = $lastPurchases->get($customer->usuario_id);
            $parts = preg_split('/\s+/', trim((string) $customer->nombre_contacto), -1, PREG_SPLIT_NO_EMPTY);

            return [$customer->id => [
                'type' => $customer->tipo === 'mayorista'
                    ? ['label' => 'Mayorista', 'class' => 'wholesale']
                    : ['label' => 'Minorista', 'class' => 'retail'],
                'initials' => collect(array_slice($parts ?: ['CL'], 0, 2))
                    ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                    ->implode(''),
                'last_purchase_label' => $this->purchaseDateLabel($lastPurchaseForCustomer?->creado_en),
            ]];
        })->all();

        return view('gestion.comercial.clientes.listar', [
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
            'customerPresentation' => $customerPresentation,
            'wholesalePercent' => $this->percentage($wholesaleCustomers, $totalCustomers),
            'retailPercent' => $this->percentage($retailCustomers, $totalCustomers),
            'commercialLevel' => $totalPurchases > 0 ? 'Activo' : 'Sin compras',
            'lastPurchaseLabel' => $this->purchaseDateLabel($lastPurchase?->creado_en),
        ]);
    }

    private function percentage(int $value, int $total): float
    {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0.0;
    }

    private function purchaseDateLabel(mixed $date): string
    {
        if ($date === null) {
            return 'Sin compras';
        }

        return $date->format('d M Y');
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, string $format, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $filters = $this->filters($request);
        $customers = $this->customerQuery($filters, $usuario)
            ->with('user:id,nombre,correo')
            ->orderBy('nombre_contacto')->orderBy('id')->limit(5000)->get();
        $rows = $customers->map(static fn (Cliente $customer): array => [
            $customer->tipo,
            $customer->documento,
            $customer->nombre_contacto,
            $customer->razon_social,
            $customer->correo,
            $customer->telefono,
            $customer->ciudad,
            $customer->activo ? 'Activo' : 'Inactivo',
            $customer->user?->nombre,
        ])->all();
        $response = $exporter->download($format, 'clientes-'.now()->format('Ymd'),
            ['Tipo', 'Documento', 'Nombre de contacto', 'Razón social', 'Correo', 'Teléfono', 'Ciudad', 'Estado', 'Usuario'],
            $rows,
            [
                'title' => 'REPORTE DE CLIENTES',
                'subtitle' => 'Clientes reales consultados desde MariaDB.',
                'module' => 'Operaciones Comerciales',
                'report_name' => 'Clientes',
                'filters' => $filters,
                'generated_by' => (string) $usuario->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Empresa, sede y permisos RBAC vigentes.',
                'indicators' => [['label' => 'Clientes incluidos', 'value' => count($rows)]],
            ]);
        $audit->record($usuario->getKey(), 'exportar', 'clientes', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarClienteRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        DB::transaction(function () use ($request, $usuario, $audit): void {
            $customer = Cliente::create($request->validated() + [
                'activo' => true,
                'creado_en' => now(),
                ...$this->autorizacion->operationScopeAttributes($usuario, 'clientes'),
            ]);
            $audit->record($usuario->id, 'customer.created', 'clientes', $customer->id, null, $customer->only(['tipo', 'tipo_documento', 'documento', 'nombre_contacto', 'correo']), $request->ip());
        });

        return back()->with('status', 'Cliente registrado.');
    }

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function import(ImportarClientesRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        $handle = fopen($request->file('archivo')->getRealPath(), 'rb');
        abort_if($handle === false, 422, 'No se pudo leer el archivo CSV.');

        try {
            $headers = fgetcsv($handle);
            $headers = array_map(static fn ($header): string => mb_strtolower(trim(ltrim((string) $header, "\xEF\xBB\xBF"))), $headers ?: []);
            $requiredHeaders = ['tipo', 'documento', 'nombre_contacto', 'razon_social', 'correo', 'telefono', 'ciudad'];
            abort_unless($headers === $requiredHeaders, 422, 'La cabecera CSV no coincide con la plantilla de clientes.');

            $rows = [];
            $documents = [];
            $line = 1;
            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                if (count(array_filter($values, static fn ($value): bool => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                abort_if(count($rows) >= 500, 422, 'La importación está limitada a 500 clientes por archivo.');
                $row = array_pad(array_slice($values, 0, count($requiredHeaders)), count($requiredHeaders), null);
                $payload = array_combine($requiredHeaders, array_map(static fn ($value): string => trim((string) $value), $row));
                $validator = Validator::make($payload, [
                    'tipo' => ['required', 'in:minorista,mayorista'],
                    'documento' => ['required', 'string', 'max:20'],
                    'razon_social' => ['nullable', 'string', 'max:160'],
                    'nombre_contacto' => ['required', 'string', 'max:160'],
                    'correo' => ['required', 'email', 'max:160'],
                    'telefono' => ['nullable', 'string', 'max:30'],
                    'ciudad' => ['nullable', 'string', 'max:80'],
                ]);
                abort_if($validator->fails(), 422, 'Error en la fila '.$line.': '.$validator->errors()->first());
                abort_if(in_array($payload['documento'], $documents, true), 422, 'El documento se repite en la fila '.$line.'.');
                $documents[] = $payload['documento'];
                $rows[] = $payload;
            }
            abort_if($rows === [], 422, 'El archivo CSV no contiene clientes para importar.');

            $scope = $this->autorizacion->operationScopeAttributes($usuario, 'clientes');
            abort_unless($this->autorizacion->allows($usuario, 'gestion.clientes.crear'), 403);
            DB::transaction(function () use ($rows, $scope, $usuario, $audit, $request): void {
                $documents = collect($rows)->pluck('documento');
                abort_if(Cliente::query()->whereIn('documento', $documents)->exists(), 422, 'Uno o más documentos ya existen en MariaDB.');
                foreach ($rows as $row) {
                    Cliente::create($row + ['activo' => true, 'creado_en' => now()] + $scope);
                }
                $audit->record($usuario->getKey(), 'customer.imported', 'clientes', null, null, ['cantidad' => count($rows)], $request->ip());
            });
        } finally {
            fclose($handle);
        }

        return back()->with('status', 'Se importaron '.count($rows).' clientes en MariaDB.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $customer  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarClienteRequest $request, Cliente $customer, RegistradorAuditoria $audit): RedirectResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);
        $this->verificarAccesoCliente($customer, $usuario, 'gestion.clientes.actualizar');

        DB::transaction(function () use ($request, $customer, $usuario, $audit): void {
            $before = $customer->only(['tipo', 'tipo_documento', 'documento', 'nombre_contacto', 'correo', 'activo']);
            $customer->update($request->validated());
            $audit->record($usuario->id, 'customer.updated', 'clientes', $customer->id, $before, $customer->only(array_keys($before)), $request->ip());
        });

        return back()->with('status', 'Cliente actualizado.');
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $customer  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
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

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $cliente  Parámetro recibido por la operación.
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $permiso  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    private function verificarAccesoCliente(Cliente $cliente, User $usuario, string $permiso): void
    {
        $visible = $this->consultaClientesEnAlcance($usuario, $permiso)
            ->whereKey($cliente->getKey())
            ->exists();

        abort_unless($visible, 404);
    }

    /**
     * Ejecuta la operación consultaClientesEnAlcance definida por la clase ClienteController.
     *
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $permiso  Parámetro recibido por la operación.
     * @return Builder Resultado de la operación.
     */
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
