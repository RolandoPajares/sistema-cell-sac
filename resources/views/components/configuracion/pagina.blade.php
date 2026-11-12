
<section class="tc-cs" data-configuracion-sistema data-cs-type="{{ $tipo }}">
    <nav class="tc-cs__breadcrumb" aria-label="Ruta de navegación">
        <i class="bi bi-house" aria-hidden="true"></i>
        <span>Configuración del sistema</span>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
        <strong>{{ $pagina['titulo'] }}</strong>
    </nav>

    <header class="tc-cs__header">
        <div class="tc-cs__title">
            <span class="tc-cs__title-icon"><i class="bi {{ $pagina['icono'] }}" aria-hidden="true"></i></span>
            <div>
                <h1>{{ $pagina['titulo'] }}</h1>
                <p>{{ $pagina['descripcion'] }}</p>
            </div>
        </div>
        <div class="tc-cs__actions">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($pagina['acciones'] as $accion)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($accion['tipo'] === 'nueva')
                    <button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-open-integration>
                        <i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}
                    </button>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @elseif ($accion['tipo'] === 'crear')
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                    <form method="post" action="{{ route('management.backups.store') }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                        @csrf
                        <button type="submit" class="tc-cs__button tc-cs__button--primary">
                            <i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}
                        </button>
                    </form>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @elseif ($accion['tipo'] === 'empresa')
                    <a class="tc-cs__button tc-cs__button--primary" href="{{ route('management.company_settings.data') }}">
                        <i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}
                    </a>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @elseif ($accion['tipo'] === 'sesiones')
                    <a class="tc-cs__button tc-cs__button--primary" href="{{ route('management.security_sessions') }}">
                        <i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </header>

    <div class="tc-cs__notice">
        <i class="bi bi-database-check" aria-hidden="true"></i>
        <strong>Datos de MariaDB</strong>
        <span>Las cifras mostradas corresponden a registros consultados; las operaciones sin persistencia disponible permanecen deshabilitadas.</span>
    </div>

    <div class="tc-cs__metrics">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($metricas as $metrica)
            <article class="tc-cs__metric tc-cs__metric--{{ $metrica['tono'] }}">
                <span><i class="bi {{ $metrica['icono'] }}" aria-hidden="true"></i></span>
                <div>
                    <small>{{ $metrica['etiqueta'] }}</small>
                    <strong>{{ $metrica['valor'] }}</strong>
                    <em>{{ $metrica['ayuda'] }}</em>
                </div>
            </article>
        @endforeach
    </div>

{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
    @include('components.configuracion.' . $tipo)

    <dialog class="tc-cs__dialog" data-cs-dialog aria-labelledby="tc-cs-dialog-title" aria-describedby="tc-cs-dialog-description">
        <div class="tc-cs__dialog-card">
            <header>
                <span class="tc-cs__title-icon"><i class="bi {{ $pagina['icono'] }}" data-cs-dialog-icon aria-hidden="true"></i></span>
                <div>
                    <h2 id="tc-cs-dialog-title" data-cs-dialog-title>Configuración</h2>
                    <p id="tc-cs-dialog-description" data-cs-dialog-description>Operación no disponible con la persistencia actual.</p>
                </div>
                <button type="button" data-cs-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="tc-cs__dialog-body">
                <nav class="tc-cs__stepper" data-cs-stepper aria-label="Pasos del formulario" hidden><ol data-cs-steps></ol></nav>
                <h3 class="tc-cs__step-title" data-cs-step-title></h3>
                <div class="tc-cs__warning" data-cs-warning role="note" hidden></div>
                <div class="tc-cs__dialog-fields" data-cs-dialog-fields></div>
                <p class="tc-cs__feedback" data-cs-feedback role="status" aria-live="polite"></p>
            </div>
            <footer>
                <button type="button" class="tc-cs__button" data-cs-previous hidden>Anterior</button>
                <button type="button" class="tc-cs__button" data-cs-close>Cancelar</button>
                <button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-next hidden>Siguiente</button>
                <button type="button" class="tc-cs__button tc-cs__button--primary" data-cs-confirm hidden disabled>Confirmar</button>
            </footer>
        </div>
    </dialog>
</section>
