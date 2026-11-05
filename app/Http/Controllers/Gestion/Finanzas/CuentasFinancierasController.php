<?php

namespace App\Http\Controllers\Gestion\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Servicios\ServicioConsultasCuentas;
use Illuminate\Contracts\View\View;

class CuentasFinancierasController extends Controller
{
    public function __construct(private ServicioConsultasCuentas $consultasCuentas) {}

    public function index(): View
    {
        $datosConfiguracion = require resource_path('views/paneles/administrador/finanzas-facturacion/componentes/datos.php');
        $pagina = $datosConfiguracion['cuentas'];
        $datosReales = $this->consultasCuentas->obtenerVista(User::query()->findOrFail(auth()->id()));

        $pagina['filas'] = $datosReales['filas'];
        $pagina['metricas'] = $datosReales['metricas'];
        $pagina['paneles'] = $datosReales['paneles'];
        $pagina['datosReales'] = true;
        foreach (['tipo', 'estado', 'moneda'] as $indice => $campo) {
            $pagina['filtros'][$indice]['opciones'] = collect($datosReales['filas'])
                ->pluck('filtros.'.$campo)->unique()->sort()->values()->all();
        }
        $pagina['descripcion'] = 'Seguimiento de saldos derivados de documentos electrónicos, compras y aplicaciones de pagos registrados.';
        $pagina['tablaTitulo'] = 'Cuentas por cobrar y pagar';
        $pagina['aviso'] = 'Los datos y saldos mostrados se consultan desde MariaDB. Las operaciones de alta y abono requieren completar sus flujos transaccionales.';
        $pagina['aside']['descripcion'] = 'El registro de obligaciones y aplicación de abonos aún no está conectado a un flujo persistente.';
        $pagina['aside']['accion'] = 'Operación no disponible';

        return view('paneles.administrador.finanzas-facturacion.cuentas.listar', ['paginaReal' => $pagina]);
    }
}
