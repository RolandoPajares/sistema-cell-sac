<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\Pedido;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoPedidoController extends Controller
{
    /** Muestra el formulario público para localizar un pedido. */
    public function index(): View
    {
        return view('publico.comercio.seguimiento-publico', ['order' => null]);
    }

    /** Busca un pedido usando el número visible y el correo asociado a la compra. */
    public function search(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'numero_pedido' => ['required', 'string', 'max:40'],
            'correo' => ['required', 'email', 'max:160'],
        ]);
        $orderNumber = trim($validated['numero_pedido']);
        $orderId = preg_match('/^(?:#\s*)?(?:CELL|PED)\s*-\s*(\d+)$/i', $orderNumber, $matches) === 1
            ? $matches[1]
            : (ctype_digit($orderNumber) ? $orderNumber : null);
        $order = Pedido::query()
            ->with(['details.product', 'user'])
            ->whereKey($orderId === null ? 0 : (int) $orderId)
            ->where(function ($query) use ($validated): void {
                $query->where('cliente_invitado_correo', $validated['correo'])
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('correo', $validated['correo']));
            })
            ->first();

        if (! $order) {
            return redirect()->route('orders.tracking.index')
                ->withInput()
                ->withErrors(['numero_pedido' => 'No encontramos un pedido con esos datos. Verifica el número y el correo.']);
        }

        return view('publico.comercio.seguimiento-publico', compact('order'));
    }

    public function show(Request $request, Pedido $order): View
    {
        // Guarda en $token el resultado necesario para continuar esta operacion.
        $token = $request->string('token')->toString();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(preg_match('/^[a-f0-9]{64}$/', $token) === 1, 404);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($order->usuario_id === null && hash_equals((string) $order->token_acceso_publico, hash('sha256', 'guest:'.$token)), 404);
        $order->load('details.product');

        // Devuelve la vista 'publico.comercio.seguimiento-invitado' con los datos preparados por este controlador.
        return view('publico.comercio.seguimiento-invitado', compact('order'));
    }
}
