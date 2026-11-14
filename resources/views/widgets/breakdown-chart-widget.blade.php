{{--
    MortalKiller\FilamentAppearance\Widgets\BreakdownChartWidget. The ring is one SVG circle per
    segment on a circumference of 100; the legend carries the amounts, so nothing depends on
    telling colours apart. Hovering a segment or its legend line highlights the segment and shows
    its amount and share in a tooltip that points at the middle of its arc, like the tooltips of
    Filament's chart widgets. Styles: resources/css/breakdown.css.
--}}
<x-filament-widgets::widget class="fap-breakdown-widget">
    <x-filament::section :heading="$heading" :description="$description">
        @if ($segments === [])
            <p class="fap-breakdown-empty">{{ $emptyStateLabel }}</p>
        @else
            <div
                class="fap-breakdown"
                x-data="{
                    active: null,
                    {{-- A point on the ring, for the tooltip to hang from. --}}
                    anchor(x, y) {
                        const box = this.$root.querySelector('svg').getBoundingClientRect()
                        const left = box.left + box.width * x
                        const top = box.top + box.height * y

                        return { width: 0, height: 0, x: left, y: top, left, right: left, top, bottom: top }
                    },
                }"
            >
                <div class="fap-breakdown-chart">
                    <svg viewBox="0 0 42 42" role="img" aria-label="{{ collect($segments)->map(fn (array $segment): string => $segment['label'].' '.$segment['percent'].'%')->implode(', ') }}">
                        @foreach ($segments as $index => $segment)
                            <circle
                                cx="21"
                                cy="21"
                                r="15.9155"
                                fill="none"
                                stroke-width="6"
                                stroke="{{ $segment['color'] }}"
                                stroke-dasharray="{{ $segment['share'] }} {{ 100 - $segment['share'] }}"
                                stroke-dashoffset="{{ $segment['offset'] }}"
                                x-bind:class="{ 'fap-breakdown-active': active === {{ $index }} }"
                                x-on:mouseenter="active = {{ $index }}"
                                x-on:mouseleave="active = null"
                                x-tooltip="{
                                    content: @js($segment['tooltip']),
                                    allowHTML: true,
                                    theme: 'fap-chart',
                                    placement: @js($segment['anchor']['x'] < 0.5 ? 'left' : 'right'),
                                    getReferenceClientRect: () => anchor({{ $segment['anchor']['x'] }}, {{ $segment['anchor']['y'] }}),
                                }"
                            ></circle>
                        @endforeach
                    </svg>

                    <div class="fap-breakdown-total">
                        <span class="fap-breakdown-total-value">{{ $total }}</span>
                        <span class="fap-breakdown-total-label">{{ $totalLabel }}</span>
                    </div>
                </div>

                <dl class="fap-breakdown-legend">
                    @foreach ($segments as $index => $segment)
                        <div
                            class="fap-breakdown-legend-item"
                            x-on:mouseenter="active = {{ $index }}"
                            x-on:mouseleave="active = null"
                            x-tooltip="{
                                content: @js($segment['tooltip']),
                                allowHTML: true,
                                theme: 'fap-chart',
                                placement: @js($segment['anchor']['x'] < 0.5 ? 'left' : 'right'),
                                getReferenceClientRect: () => anchor({{ $segment['anchor']['x'] }}, {{ $segment['anchor']['y'] }}),
                            }"
                        >
                            <dt>
                                <span class="fap-breakdown-swatch" style="background-color: {{ $segment['color'] }}" aria-hidden="true"></span>
                                {{ $segment['label'] }}
                            </dt>
                            <dd>
                                <span class="fap-breakdown-value">{{ $segment['value'] }}</span>
                                <span class="fap-breakdown-percent">{{ $segment['percent'] }}%</span>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
