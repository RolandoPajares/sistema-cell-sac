<?php

namespace App\Http\Controllers\Paneles;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Servicios\ServicioPaneles;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class PanelDashboardController extends Controller
{
    public function __construct(protected readonly ServicioPaneles $panelService) {}

    final public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->isRole($this->role()), 403);

        return view($this->viewName(), $this->viewData($request, $user));
    }

    abstract protected function role(): string;

    abstract protected function viewName(): string;

    /**
     * @return array<string, mixed>
     */
    protected function viewData(Request $request, User $user): array
    {
        return $this->panelService->roleDashboard(
            $user,
            (array) $request->session()->get('cart', []),
        );
    }
}
