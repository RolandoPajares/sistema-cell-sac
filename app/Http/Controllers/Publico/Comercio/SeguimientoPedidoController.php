<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\Pedido;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoPedidoController extends Controller
{
    //
    public function show(Request $request, Pedido $order): View
    {
        $token = $request->string('token')->toString();
        abort_unless(preg_match('/^[a-f0-9]{64}$/', $token) === 1, 404);
        abort_unless($order->usuario_id === null && hash_equals((string) $order->token_acceso_publico, hash('sha256', 'guest:'.$token)), 404);
        $order->load('details.product');

        return view('publico.comercio.seguimiento-invitado', compact('order'));
    }
}
