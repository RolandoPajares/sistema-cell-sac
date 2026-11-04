@extends('layouts.administracion')

@section('title', 'Asignación de responsables')
@section('body_class', 'modulo-organizacion')

@section('content')
<section class="tc-org" data-organizacion="responsables">
    @include('paneles.administrador.organizacion.componentes.cabecera', [
        'titulo' => 'Asignación de responsables',
        'icono' => 'bi-person-check',
        'descripcion' => 'La asignación de responsables requiere una relación empresarial definida en el esquema.',
        'acciones' => [
            ['texto' => 'Administrar asignaciones de rol', 'icono' => 'bi-person-gear', 'ruta' => route('management.user_assignments')],
        ],
    ])

    <div class="tc-org__alert" role="status">
        <strong>No hay un contrato de datos para responsables.</strong>
        <p>MariaDB contiene asignaciones de usuarios a roles y sedes. No define una relación para asignar responsables a almacenes, áreas, niveles de aprobación ni recursos de la organización. Esta pantalla no registrará datos hasta que exista esa regla empresarial y una tabla compatible.</p>
        <p>Las asignaciones de roles por empresa o sede se administran en el módulo de seguridad.</p>
        <a class="tc-org__button tc-org__button--primary" href="{{ route('management.user_assignments') }}">Abrir asignaciones de roles</a>
    </div>
</section>
@endsection
