<?php

namespace App\Http\Controllers\Gestion\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Inventario\AccionTransferenciaExistenciaRequest;
use App\Http\Requests\Gestion\Inventario\GuardarTransferenciaExistenciaRequest;
use App\Models\Inventario\TransferenciaExistencia;
use App\Models\User;
use App\Servicios\ServicioTransferenciasExistencias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransferenciaExistenciaController extends Controller
{
    public function index(Request $request, ServicioTransferenciasExistencias $transfers): View
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:solicitada,aprobada,en tránsito,recibida parcialmente,recibida totalmente,cerrada,rechazada,cancelada'],
        ]);

        return view('paneles.administrador.inventario-logistica.transferencias.listar', [
            ...$transfers->pageData($this->actor($request), [
                'buscar' => trim((string) ($filters['buscar'] ?? '')) ?: null,
                'estado' => $filters['estado'] ?? null,
            ]),
            'filters' => $filters,
        ]);
    }

    public function store(GuardarTransferenciaExistenciaRequest $request, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfer = $transfers->request($request->validated(), $this->actor($request), $request->ip());

        return $this->success('Se registro la solicitud '.$transfer->codigo.'. No se modifico el inventario.');
    }

    public function approve(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfers->approve($transfer, $this->actor($request), $request->validated('clave_idempotencia'), $request->ip());

        return $this->success('Transferencia aprobada. El inventario sigue sin cambios hasta el despacho.');
    }

    public function dispatch(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfers->dispatch($transfer, $request->validated(), $this->actor($request), $request->ip());

        return $this->success('Despacho registrado; las cantidades quedaron en transito.');
    }

    public function receive(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $updated = $transfers->receive($transfer, $request->validated(), $this->actor($request), $request->ip());

        return $this->success('Recepcion registrada: '.$updated->estado.'.');
    }

    public function returnToOrigin(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfers->returnToOrigin($transfer, $request->validated(), $this->actor($request), $request->ip());

        return $this->success('Devolucion al almacen de origen registrada y auditada.');
    }

    public function reconcileDifference(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfers->reconcileDifference($transfer, $request->validated(), $this->actor($request), $request->ip());

        return $this->success('Diferencia conciliada y auditada; no se modificaron existencias.');
    }

    public function reject(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfers->reject($transfer, $request->validated(), $this->actor($request), $request->ip());

        return $this->success('Solicitud rechazada. No se modifico el inventario.');
    }

    public function cancel(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfers->cancel($transfer, $request->validated(), $this->actor($request), $request->ip());

        return $this->success('Transferencia cancelada. No se modifico el inventario.');
    }

    public function close(AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers): RedirectResponse
    {
        $transfers->close($transfer, $request->validated(), $this->actor($request), $request->ip());

        return $this->success('Transferencia conciliada y cerrada.');
    }

    public function export(Request $request, ServicioTransferenciasExistencias $transfers): StreamedResponse
    {
        $filters = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'in:solicitada,aprobada,en tránsito,recibida parcialmente,recibida totalmente,cerrada,rechazada,cancelada'],
        ]);
        $records = $transfers->exportQuery($this->actor($request), $filters)->with([
            'sedeOrigen:id,nombre', 'sedeDestino:id,nombre', 'almacenOrigen:id,nombre', 'almacenDestino:id,nombre',
        ])->lazy(500);

        return response()->streamDownload(function () use ($records): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                return;
            }
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Codigo', 'Sede origen', 'Almacen origen', 'Sede destino', 'Almacen destino', 'Estado', 'Solicitado', 'Despachado', 'Recibido']);
            foreach ($records as $transfer) {
                fputcsv($output, [
                    $transfer->codigo, $transfer->sedeOrigen?->nombre, $transfer->almacenOrigen?->nombre,
                    $transfer->sedeDestino?->nombre, $transfer->almacenDestino?->nombre, $transfer->estado,
                    $transfer->creado_en?->toDateTimeString(), $transfer->enviado_en?->toDateTimeString(), $transfer->recibido_en?->toDateTimeString(),
                ]);
            }
            fclose($output);
        }, 'transferencias.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function actor(Request $request): User
    {
        abort_unless($request->user() instanceof User, 401);

        return $request->user();
    }

    private function success(string $message): RedirectResponse
    {
        return redirect()->route('management.inventory.preview.transfers')->with('status', $message);
    }
}
