<?php

namespace App\Http\Controllers\Gestion\Comercial\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Comercial\Clientes\ActualizarClienteRequest;
use App\Http\Requests\Gestion\Comercial\Clientes\GuardarClienteRequest;
use App\Http\Requests\Gestion\Comercial\Clientes\ImportarClientesRequest;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
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
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        $consultaClientes = fn (): Builder => $this->consultaClientesEnAlcance($usuario, 'gestion.clientes.consultar');
        $consultaPedidos = fn (): Builder => $this->autorizacion->scopeRecordsFor(
            Pedido::query(), $usuario, 'gestion.pedidos.consultar', 'usuario_id',
        );
        // Guarda en $customers el resultado necesario para continuar esta operacion.
        $customers = $this->consultaClientes($filters, $usuario)
            ->with('user:id,nombre,correo,rol')
            ->orderBy('nombre_contacto')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        // Guarda en $userIds el resultado necesario para continuar esta operacion.
        $userIds = $customers->getCollection()->pluck('usuario_id')->filter()->values();
        // Guarda en $lastPurchases el resultado necesario para continuar esta operacion.
        $lastPurchases = $userIds->isEmpty()
            ? collect()
            : $consultaPedidos()
                ->whereIn('usuario_id', $userIds)
                ->latest('creado_en')
                ->latest('id')
                ->get(['id', 'usuario_id', 'total', 'creado_en'])
                ->groupBy('usuario_id')
                ->map(static fn (Collection $orders): ?Pedido => $orders->first());
        // Guarda en $totalPurchases el resultado necesario para continuar esta operacion.
        $totalPurchases = (float) $consultaPedidos()->sum('total');
        // Guarda en $lastPurchase el resultado necesario para continuar esta operacion.
        $lastPurchase = $consultaPedidos()->latest('creado_en')->latest('id')->first(['id', 'total', 'creado_en']);
        // Guarda en $recentOrders el resultado necesario para continuar esta operacion.
        $recentOrders = $consultaPedidos()->with('user:id,nombre')->latest('creado_en')->latest('id')->limit(3)
            ->get(['id', 'usuario_id', 'total', 'creado_en']);
        // Guarda en $recentCustomers el resultado necesario para continuar esta operacion.
        $recentCustomers = $consultaClientes()->latest('creado_en')->limit(3)
            ->get(['id', 'nombre_contacto', 'correo', 'creado_en']);
        // Guarda en $recentActivity el resultado necesario para continuar esta operacion.
        $recentActivity = $this->actividadReciente($recentOrders, $recentCustomers);
        // Guarda en $totalCustomers el resultado necesario para continuar esta operacion.
        $totalCustomers = $consultaClientes()->count();
        // Guarda en $activeCustomers el resultado necesario para continuar esta operacion.
        $activeCustomers = $consultaClientes()->where('activo', true)->count();
        // Guarda en $wholesaleCustomers el resultado necesario para continuar esta operacion.
        $wholesaleCustomers = $consultaClientes()->where('tipo', 'mayorista')->count();
        // Guarda en $retailCustomers el resultado necesario para continuar esta operacion.
        $retailCustomers = $consultaClientes()->where('tipo', 'minorista')->count();
        // Guarda en $activeAccounts el resultado necesario para continuar esta operacion.
        $activeAccounts = $consultaClientes()->where('activo', true)->whereNotNull('usuario_id')->count();
        // Guarda en $customerPresentation el resultado necesario para continuar esta operacion.
        $customerPresentation = $customers->getCollection()->mapWithKeys(function (Cliente $customer) use ($lastPurchases): array {
            // Guarda en $lastPurchaseForCustomer el resultado necesario para continuar esta operacion.
            $lastPurchaseForCustomer = $lastPurchases->get($customer->usuario_id);
            // Guarda en $parts el resultado necesario para continuar esta operacion.
            $parts = preg_split('/\s+/', trim((string) $customer->nombre_contacto), -1, PREG_SPLIT_NO_EMPTY);

            return [$customer->id => [
                'type' => $customer->tipo === 'mayorista'
                    ? ['label' => 'Mayorista', 'class' => 'wholesale']
                    : ['label' => 'Minorista', 'class' => 'retail'],
                'initials' => collect(array_slice($parts ?: ['CL'], 0, 2))
                    ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                    ->implode(''),
                'last_purchase_label' => $this->etiquetaFechaCompra($lastPurchaseForCustomer?->creado_en),
            ]];
        })->all();

        // Devuelve la vista 'gestion.comercial.clientes.listar' con los datos preparados por este controlador.
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
            'wholesalePercent' => $this->porcentaje($wholesaleCustomers, $totalCustomers),
            'retailPercent' => $this->porcentaje($retailCustomers, $totalCustomers),
            'commercialLevel' => $totalPurchases > 0 ? 'Activo' : 'Sin compras',
            'lastPurchaseLabel' => $this->etiquetaFechaCompra($lastPurchase?->creado_en),
        ]);
    }

    private function porcentaje(int $value, int $total): float
    {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0.0;
    }

    private function etiquetaFechaCompra(mixed $date): string
    {
        // Comprueba la condicion $date === null antes de ejecutar este bloque.
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
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        // Guarda en $filters el resultado necesario para continuar esta operacion.
        $filters = $this->filtros($request);
        // Guarda en $customers el resultado necesario para continuar esta operacion.
        $customers = $this->consultaClientes($filters, $usuario)
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
        // Guarda en $response el resultado necesario para continuar esta operacion.
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
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $usuario, $audit): void {
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $customer = Cliente::create($request->validated() + [
                'activo' => true,
                'creado_en' => now(),
                ...$this->autorizacion->operationScopeAttributes($usuario, 'clientes'),
            ]);
            $audit->record($usuario->id, 'customer.created', 'clientes', $customer->id, null, $customer->only(['tipo', 'tipo_documento', 'documento', 'nombre_contacto', 'correo']), $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Guarda en $handle el resultado necesario para continuar esta operacion.
        $handle = fopen($request->file('archivo')->getRealPath(), 'rb');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($handle === false, 422, 'No se pudo leer el archivo CSV.');

        // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
        try {
            // Guarda en $headers el resultado necesario para continuar esta operacion.
            $headers = fgetcsv($handle);
            $headers = array_map(static fn ($header): string => mb_strtolower(trim(ltrim((string) $header, "\xEF\xBB\xBF"))), $headers ?: []);
            // Agrupa en $requiredHeaders los datos que se utilizaran en la siguiente operacion.
            $requiredHeaders = ['tipo', 'documento', 'nombre_contacto', 'razon_social', 'correo', 'telefono', 'ciudad'];
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($headers === $requiredHeaders, 422, 'La cabecera CSV no coincide con la plantilla de clientes.');

            // Agrupa en $rows los datos que se utilizaran en la siguiente operacion.
            $rows = [];
            // Agrupa en $documents los datos que se utilizaran en la siguiente operacion.
            $documents = [];
            // Guarda en $line el resultado necesario para continuar esta operacion.
            $line = 1;
            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                // Comprueba la condicion count(array_filter($values, static fn ($value): bool => trim((string) $value) !== '')) === 0 antes de ejecutar este bloque.
                if (count(array_filter($values, static fn ($value): bool => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_if(count($rows) >= 500, 422, 'La importación está limitada a 500 clientes por archivo.');
                // Guarda en $row el resultado necesario para continuar esta operacion.
                $row = array_pad(array_slice($values, 0, count($requiredHeaders)), count($requiredHeaders), null);
                $payload = array_combine($requiredHeaders, array_map(static fn ($value): string => trim((string) $value), $row));
                // Guarda en $validator el resultado necesario para continuar esta operacion.
                $validator = Validator::make($payload, [
                    'tipo' => ['required', 'in:minorista,mayorista'],
                    'documento' => ['required', 'string', 'max:20'],
                    'razon_social' => ['nullable', 'string', 'max:160'],
                    'nombre_contacto' => ['required', 'string', 'max:160'],
                    'correo' => ['required', 'email', 'max:160'],
                    'telefono' => ['nullable', 'string', 'max:30'],
                    'ciudad' => ['nullable', 'string', 'max:80'],
                ]);
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_if($validator->fails(), 422, 'Error en la fila '.$line.': '.$validator->errors()->first());
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_if(in_array($payload['documento'], $documents, true), 422, 'El documento se repite en la fila '.$line.'.');
                $documents[] = $payload['documento'];
                $rows[] = $payload;
            }
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($rows === [], 422, 'El archivo CSV no contiene clientes para importar.');

            // Guarda en $scope el resultado necesario para continuar esta operacion.
            $scope = $this->autorizacion->operationScopeAttributes($usuario, 'clientes');
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($this->autorizacion->allows($usuario, 'gestion.clientes.crear'), 403);
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::transaction(function () use ($rows, $scope, $usuario, $audit, $request): void {
                // Guarda en $documents el resultado necesario para continuar esta operacion.
                $documents = collect($rows)->pluck('documento');
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_if(Cliente::query()->whereIn('documento', $documents)->exists(), 422, 'Uno o más documentos ya existen en MariaDB.');
                // Recorre $rows as $row y procesa cada elemento de la coleccion.
                foreach ($rows as $row) {
                    Cliente::create($row + ['activo' => true, 'creado_en' => now()] + $scope);
                }
                $audit->record($usuario->getKey(), 'customer.imported', 'clientes', null, null, ['cantidad' => count($rows)], $request->ip());
            });
        } finally {
            fclose($handle);
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->verificarAccesoCliente($customer, $usuario, 'gestion.clientes.actualizar');

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $customer, $usuario, $audit): void {
            // Guarda en $before el resultado necesario para continuar esta operacion.
            $before = $customer->only(['tipo', 'tipo_documento', 'documento', 'nombre_contacto', 'correo', 'activo']);
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $customer->update($request->validated());
            $audit->record($usuario->id, 'customer.updated', 'clientes', $customer->id, $before, $customer->only(array_keys($before)), $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene en $usuario la informacion de la solicitud o del usuario actual.
        $usuario = $request->user();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($usuario instanceof User, 403);
        // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
        $this->verificarAccesoCliente($customer, $usuario, 'gestion.clientes.eliminar');

        $customer->update(['activo' => false]);
        $audit->record($usuario->id, 'customer.deactivated', 'clientes', $customer->id, ['activo' => true], ['activo' => false], $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Cliente desactivado.');
    }

    /**
     * @param  array{buscar:?string,tipo:?string,estado:?string,ciudad:?string,por_pagina:int}  $filters
     * @return Builder<Cliente>
     */
    private function consultaClientes(array $filters, User $usuario): Builder
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
        // Guarda en $visible el resultado necesario para continuar esta operacion.
        $visible = $this->consultaClientesEnAlcance($usuario, $permiso)
            ->whereKey($cliente->getKey())
            ->exists();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
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
        // Comprueba la condicion $this->autorizacion->allowsGlobal($usuario, $permiso) antes de ejecutar este bloque.
        if ($this->autorizacion->allowsGlobal($usuario, $permiso)) {
            return $this->autorizacion->scopeRecordsFor(Cliente::query(), $usuario, $permiso);
        }

        // Guarda en $ubicacion el resultado necesario para continuar esta operacion.
        $ubicacion = $this->autorizacion->operationSite($usuario);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
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
    private function filtros(Request $request): array
    {
        // Guarda en $search el resultado necesario para continuar esta operacion.
        $search = trim($request->string('buscar')->toString());
        // Guarda en $type el resultado necesario para continuar esta operacion.
        $type = trim($request->string('tipo')->toString());
        // Guarda en $status el resultado necesario para continuar esta operacion.
        $status = trim($request->string('estado')->toString());
        // Guarda en $city el resultado necesario para continuar esta operacion.
        $city = trim($request->string('ciudad')->toString());
        // Guarda en $perPage el resultado necesario para continuar esta operacion.
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
    private function actividadReciente(Collection $recentOrders, Collection $recentCustomers): Collection
    {
        // Guarda en $activities el resultado necesario para continuar esta operacion.
        $activities = collect();

        // Recorre $recentOrders as $order y procesa cada elemento de la coleccion.
        foreach ($recentOrders as $order) {
            $activities->push([
                'icon' => 'cart3',
                'color' => 'green',
                'title' => 'Compra registrada',
                'detail' => ($order->user?->nombre ?: 'Cliente').' · S/ '.number_format((float) $order->total, 2),
                'date' => $order->creado_en,
            ]);
        }

        // Recorre $recentCustomers as $customer y procesa cada elemento de la coleccion.
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
