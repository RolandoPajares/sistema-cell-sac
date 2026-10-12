<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $role = $request->user()->rol;
        $query = Order::with('details.product', 'user')->latest('creado_en')->latest('id');

        if ($role === 'ventas_minoristas') {
            $query->whereHas('user', fn ($builder) => $builder->where('rol', 'cliente_minorista'));
        } elseif ($role === 'ventas_mayoristas') {
            $query->whereHas('user', fn ($builder) => $builder->where('rol', 'cliente_mayorista'));
        } elseif (str_starts_with($role, 'cliente_')) {
            $query->whereBelongsTo($request->user(), 'user');
        } elseif ($role !== 'administrador') {
            abort(403);
        }

        return view('commerce.orders', ['orders' => $query->paginate(20)]);
    }

    public function show(Order $order): View
    {
        $order->loadMissing('user');
        $this->authorize('view', $order);

        return view('commerce.order', ['order' => $order->load('details.product', 'user')]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order->loadMissing('user');
        $this->authorize('updateStatus', $order);
        $this->orderService->updateStatus($order, $request->user(), $request->validated('estado'), $request->ip());

        return back()->with('status', 'Estado de pedido actualizado.');
    }
}
