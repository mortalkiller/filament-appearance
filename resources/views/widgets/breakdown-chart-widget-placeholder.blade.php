{{--
    What a lazy BreakdownChartWidget shows while it loads: the real heading, then the outline of
    the chart (a ring and a few legend lines) pulsing. A placeholder is rendered outside the
    component, so it takes the widget's place in the grid from the data it is given.
    Styles: resources/css/breakdown.css.
--}}
@php
    use Filament\Support\View\ComponentAttributeBag;
@endphp

<div {{ (new ComponentAttributeBag)->gridColumn($columnSpan, $columnStart)->class(['fi-wi-widget fap-breakdown-widget']) }}>
    <x-filament::section :heading="$heading" :description="$description">
        <div class="fap-breakdown fap-breakdown-skeleton" role="status" aria-busy="true" aria-label="{{ __('filament-appearance::appearance.breakdown.loading') }}">
            <div class="fap-breakdown-chart">
                <span class="fap-breakdown-skeleton-ring"></span>
            </div>

            <div class="fap-breakdown-legend">
                @for ($line = 0; $line < $lines; $line++)
                    <div class="fap-breakdown-legend-item">
                        <span class="fap-breakdown-skeleton-bone" style="width: {{ 45 - $line * 8 }}%"></span>
                        <span class="fap-breakdown-skeleton-bone" style="width: 5.5rem"></span>
                    </div>
                @endfor
            </div>
        </div>
    </x-filament::section>
</div>
