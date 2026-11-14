{{--
    In the page head: the shell features that are on (the stylesheet looks for them in the meta
    tag), whether the sidebar was left collapsed, and the colours of the palette in use, as CSS
    variables read by the package stylesheet.
--}}
<meta name="filament-appearance" content="{{ implode(' ', $features) }}">
{{--
    A collapsible sidebar is hidden by Filament until Alpine starts, because only then is it known
    whether the user left it open or collapsed. That choice is in the browser's storage, so it is
    read here, before the first paint, for the stylesheet to draw the sidebar in the right width
    from the start (resources/css/sidebar-collapse.css).
--}}
<script>
    try {
        document.documentElement.classList.toggle(
            'fap-sidebar-collapsed',
            window.localStorage.getItem('filament:sidebar:open') === 'false',
        )
    } catch (error) {}
</script>
<style>
    :root {
        @foreach ($palette->cssVariables() as $name => $value)
            {{ $name }}: {{ $value }};
        @endforeach
    }

    :root.dark {
        @foreach ($palette->cssVariables(dark: true) as $name => $value)
            {{ $name }}: {{ $value }};
        @endforeach
    }
</style>
