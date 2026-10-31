<?php

namespace App\Http\Controllers\Gestion\SoporteReclamos;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SoporteReclamoController extends Controller
{
    public function index(): View
    {
        $module = require resource_path('views/gestion/soporte-reclamos/datos.php');

        return view('gestion.soporte-reclamos.listar', compact('module'));
    }
}
