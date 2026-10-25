@props(['role' => 'bot', 'name' => null])

<article class="assistant-message assistant-message-{{ $role }}">
    <div class="assistant-message-avatar">{{ $role === 'user' ? 'TÚ' : 'MD' }}</div>
    <div class="assistant-bubble">
        @if($name)<b>{{ $name }}</b>@endif
        {{ $slot }}
    </div>
</article>
