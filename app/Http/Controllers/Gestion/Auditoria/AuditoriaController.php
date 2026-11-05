<?php

namespace App\Http\Controllers\Gestion\Auditoria;

use App\Http\Controllers\Controller;
use App\Models\Auditoria\RegistroAuditoria;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        $query = RegistroAuditoria::query()->with('user')->latest('creado_en')->latest('id');
        if ($request->filled('entidad')) {
            $query->where('entidad', $request->string('entidad')->toString());
        }
        if ($request->filled('desde')) {
            $query->whereDate('creado_en', '>=', $request->date('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('creado_en', '<=', $request->date('hasta'));
        }

        return view('gestion.auditoria.listar', ['logs' => $query->paginate(30)->withQueryString()]);
    }
}
