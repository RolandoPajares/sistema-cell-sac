
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
@if (session('status'))<div class="container flash-success" role="status">{{ session('status') }}</div>@endif
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
@if ($errors->any())<div class="container flash-error" role="alert">{{ $errors->first() }}</div>@endif
