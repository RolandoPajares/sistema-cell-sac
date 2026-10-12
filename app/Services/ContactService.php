<?php

namespace App\Services;

use App\Models\ContactMessage;

class ContactService
{
    /** @param array{nombre:string, contacto:string, mensaje:string} $data */
    public function register(array $data): ContactMessage
    {
        return ContactMessage::create($data + [
            'estado' => 'Nuevo',
            'creado_en' => now(),
        ]);
    }
}
