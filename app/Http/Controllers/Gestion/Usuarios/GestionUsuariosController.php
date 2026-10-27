<?php

namespace App\Http\Controllers\Gestion\Usuarios;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarUsuarioRequest;
use App\Models\Clientes\Cliente;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GestionUsuariosController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $users = $this->userQuery($filters)
            ->with('customer:id,usuario_id,documento,razon_social,tipo,ciudad,activo')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $totalUsers = User::query()->count();
        $currentMonthUsers = User::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $roleDistribution = User::query()
            ->select('rol')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('rol')
            ->orderByDesc('total')
            ->get();
        $recentUsers = User::query()
            ->with('customer:id,usuario_id,ciudad')
            ->latest('created_at')
            ->latest('id')
            ->limit(5)
            ->get(['id', 'nombre', 'correo', 'rol', 'created_at']);

        return view('gestion.usuarios.listar', [
            'users' => $users,
            'roles' => User::ROLES,
            'roleLabels' => $this->roleLabels(),
            'roleDistribution' => $roleDistribution,
            'recentUsers' => $recentUsers,
            'filters' => $filters,
            'cities' => Cliente::query()->whereNotNull('ciudad')->where('ciudad', '<>', '')->distinct()->orderBy('ciudad')->pluck('ciudad'),
            'totalUsers' => $totalUsers,
            'activeUsers' => $totalUsers,
            'inactiveUsers' => 0,
            'rolesConfigured' => count(User::ROLES),
            'currentMonthUsers' => $currentMonthUsers,
        ]);
    }

    public function store(GuardarUsuarioRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        $user = DB::transaction(function () use ($data): User {
            $user = User::create([
                'nombre' => $data['nombre'],
                'correo' => mb_strtolower($data['correo']),
                'password' => $data['password'],
                'rol' => $data['rol'],
            ]);

            if (str_starts_with($data['rol'], 'cliente_')) {
                Cliente::create([
                    'usuario_id' => $user->id,
                    'tipo' => $data['rol'] === 'cliente_mayorista' ? 'mayorista' : 'minorista',
                    'documento' => $data['documento'],
                    'razon_social' => $data['razon_social'] ?? null,
                    'nombre_contacto' => $data['nombre'],
                    'correo' => $user->correo,
                    'telefono' => $data['telefono'] ?? null,
                    'ciudad' => $data['ciudad'] ?? null,
                    'activo' => true,
                    'creado_en' => now(),
                ]);
            }

            return $user;
        });

        $audit->record($request->user()->id, 'user.created', 'users', $user->id, null, ['correo' => $user->correo, 'rol' => $user->rol], $request->ip());

        return back()->with('status', 'Cuenta creada. La contraseña no se volverá a mostrar.');
    }

    /**
     * @param  array{buscar:?string,rol:?string,estado:?string,ciudad:?string,fecha:?string,por_pagina:int}  $filters
     * @return Builder<User>
     */
    private function userQuery(array $filters): Builder
    {
        return User::query()
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('correo', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['rol'], fn (Builder $query, string $role): Builder => $query->where('rol', $role))
            ->when($filters['ciudad'], fn (Builder $query, string $city): Builder => $query->whereHas('customer', fn (Builder $customerQuery): Builder => $customerQuery->where('ciudad', $city)))
            ->when($filters['fecha'] === 'hoy', fn (Builder $query): Builder => $query->whereDate('created_at', now()->toDateString()))
            ->when($filters['fecha'] === 'ultimos_30', fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30)))
            ->when($filters['fecha'] === 'ultimos_90', fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(90)))
            ->when($filters['estado'] === 'inactivo', fn (Builder $query): Builder => $query->whereRaw('1 = 0'));
    }

    /**
     * @return array{buscar:?string,rol:?string,estado:?string,ciudad:?string,fecha:?string,por_pagina:int}
     */
    private function filters(Request $request): array
    {
        $search = trim($request->string('buscar')->toString());
        $role = trim($request->string('rol')->toString());
        $status = trim($request->string('estado')->toString());
        $city = trim($request->string('ciudad')->toString());
        $date = trim($request->string('fecha')->toString());
        $perPage = $request->integer('por_pagina', 10);

        return [
            'buscar' => $search !== '' ? $search : null,
            'rol' => in_array($role, User::ROLES, true) ? $role : null,
            'estado' => in_array($status, ['activo', 'inactivo'], true) ? $status : null,
            'ciudad' => $city !== '' ? $city : null,
            'fecha' => in_array($date, ['hoy', 'ultimos_30', 'ultimos_90'], true) ? $date : null,
            'por_pagina' => in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function roleLabels(): array
    {
        return [
            'administrador' => 'Administrador',
            'compras_logistica' => 'Compras y logística',
            'ventas_mayoristas' => 'Ventas mayoristas',
            'ventas_minoristas' => 'Ventas minoristas',
            'marketing' => 'Marketing',
            'cliente_mayorista' => 'Cliente mayorista',
            'cliente_minorista' => 'Cliente minorista',
        ];
    }
}
