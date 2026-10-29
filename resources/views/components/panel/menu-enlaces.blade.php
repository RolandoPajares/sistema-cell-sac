@foreach ($links as $link)
    @if (\Illuminate\Support\Facades\Route::has($link['route']))
        <a class="{{ request()->routeIs($link['route']) ? 'is-active' : '' }}" href="{{ route($link['route']) }}" title="{{ $link['label'] }}">
            <i class="bi {{ $link['icon'] }}" aria-hidden="true"></i>
            <span>{{ $link['label'] }}</span>
        </a>
    @endif
@endforeach
