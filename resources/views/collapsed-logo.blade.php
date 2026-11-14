{{--
    The compact logo, next to the panel's own in the topbar. It is only shown on desktop while the
    sidebar is collapsed (resources/css/sidebar-collapse.css), one copy for each mode.
--}}
@php
    use Illuminate\Contracts\Support\Htmlable;
@endphp

<{{ $url === null ? 'span' : 'a' }} @if ($url !== null) href="{{ $url }}" @endif class="fap-collapsed-logo" aria-label="{{ $label }}">
    @foreach (['light' => $logo, 'dark' => $darkLogo] as $mode => $image)
        @if ($image instanceof Htmlable)
            <span class="fap-collapsed-logo-image fap-collapsed-logo-{{ $mode }}">{{ $image }}</span>
        @else
            <img src="{{ $image }}" alt="" class="fap-collapsed-logo-image fap-collapsed-logo-{{ $mode }}">
        @endif
    @endforeach
</{{ $url === null ? 'span' : 'a' }}>
