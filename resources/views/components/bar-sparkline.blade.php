{{--
    MortalKiller\FilamentAppearance\Support\BarSparkline. Styles: resources/css/sparkline.css; the
    tooltip uses the "fap-chart" theme (breakdown.css). Optional attributes come in attribute bags:
    an @if inside a tag gets Livewire's block markers in the middle of it.
--}}
<span {{ $attributes }}>
    @foreach ($columns as $column)
        {{-- The whole column answers the pointer, so a month with almost nothing still shows its tooltip. --}}
        <span {{ $column['attributes'] }}>
            <i @class(['fap-bar-sparkline-current' => $column['isCurrent']]) style="height: {{ $column['height'] }}%"></i>
        </span>
    @endforeach
</span>
