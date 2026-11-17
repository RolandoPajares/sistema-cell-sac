<?php

namespace App\Servicios\Gestion\InventarioLogistica\Categorias;

use App\Models\Catalogo\Categoria;
use Illuminate\Support\Str;

final class ServicioPresentacionCategorias
{
    // Determina el icono visual que corresponde a la categoria recibida.
    public function icono(Categoria $categoria): string
    {
        // Devuelve el icono correspondiente a la categoria del catalogo.
        // Guarda en $nombre el resultado necesario para continuar la operacion.
        $nombre = Str::lower($categoria->nombre);

        // Selecciona el resultado segun el nombre o condicion evaluada.
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
