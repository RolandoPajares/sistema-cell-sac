<?php

namespace App\Http\Resources;

use App\Models\Catalogo\ImagenProducto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecursoProducto extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'brand' => $this->marca,
            'name' => $this->nombre,
            'description' => $this->descripcion,
            'price' => (float) $this->effective_price,
            'stock' => (int) $this->existencias,
            'image_path' => $this->url_imagen,
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => (int) $this->category->id,
                'name' => $this->category->nombre,
            ]),
            'images' => $this->whenLoaded('images', fn (): array => $this->images
                ->map(fn (ImagenProducto $image): array => [
                    'path' => $image->ruta_imagen,
                    'order' => (int) $image->orden,
                ])
                ->values()
                ->all()),
        ];
    }
}
