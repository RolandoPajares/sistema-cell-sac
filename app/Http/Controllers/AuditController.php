<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('user')->latest('creado_en')->latest('id');
        if ($request->filled('entidad')) {
            $query->where('entidad', $request->string('entidad')->toString());
        }
        if ($request->filled('desde')) {
            $query->whereDate('creado_en', '>=', $request->date('desde'));
        }
        if ($request->filled('hasta')) {
            $query->whereDate('creado_en', '<=', $request->date('hasta'));
        }

        return view('management.audit', ['logs' => $query->paginate(30)->withQueryString()]);
    }
}
