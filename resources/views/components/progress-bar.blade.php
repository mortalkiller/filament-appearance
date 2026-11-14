{{-- MortalKiller\FilamentAppearance\Support\ProgressBar. Styles: resources/css/progress.css. --}}
@if ($value === null)
    <span class="fap-progress" data-color="{{ $color }}"><i style="width: {{ $width }}%"></i></span>
@else
    <span class="fap-progress-with-value">
        <span class="fap-progress" data-color="{{ $color }}"><i style="width: {{ $width }}%"></i></span>
        <span @class(['fap-progress-value', 'fap-progress-value-negative' => $isNegative])>{{ $value }}</span>
    </span>
@endif
