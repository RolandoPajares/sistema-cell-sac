<?php

namespace App\Servicios;

use App\Models\Catalogo\Categoria;
use Illuminate\Support\Str;

final class ServicioPresentacionCategorias
{
    public function icono(Categoria $categoria): string
    {
        $nombre = Str::lower($categoria->nombre);

        return match (true) {
            Str::contains($nombre, 'audio') => 'bi-headphones',
            Str::contains($nombre, ['celular', 'smart']) => 'bi-phone',
            Str::contains($nombre, ['comput', 'laptop']) => 'bi-laptop',
            Str::contains($nombre, ['video', 'juego']) => 'bi-controller',
            Str::contains($nombre, ['wear', 'reloj']) => 'bi-smartwatch',
            Str::contains($nombre, 'hogar') => 'bi-house-heart',
            default => 'bi-box-seam',
        };
    }
}
