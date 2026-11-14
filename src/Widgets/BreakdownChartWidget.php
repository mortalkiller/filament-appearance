<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Widgets;

use Filament\Support\Facades\FilamentColor;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use MortalKiller\FilamentAppearance\Widgets\BreakdownChartWidget\Segment;

/**
 * How a total splits into parts: a ring with the total in the middle and, beside it, a legend
 * with the amount and the share of each part. Drawn in SVG and CSS, in shades of one Filament
 * colour, so it follows the panel's palette and dark mode by itself.
 *
 *     class SalesByChannel extends BreakdownChartWidget
 *     {
 *         protected ?string $heading = 'Sales by channel';
 *
 *         protected function getSegments(): array
 *         {
 *             return [Segment::make('Online', 2400), Segment::make('Shop', 850)];
 *         }
 *     }
 */
abstract class BreakdownChartWidget extends Widget
{
    /** Shades of the ramp, darkest first, by how many segments there are. */
    private const RAMPS = [
        1 => [600],
        2 => [700, 400],
        3 => [700, 500, 300],
        4 => [800, 600, 400, 200],
        5 => [800, 700, 500, 400, 200],
        6 => [900, 700, 600, 400, 300, 200],
    ];

    protected string $view = 'filament-appearance::widgets.breakdown-chart-widget';

    protected ?string $heading = null;

    protected ?string $description = null;

    /** The Filament colour the ramp is made of. */
    protected string $color = 'primary';

    /** Past this many segments, the smallest are folded into one ("Others"). At most six. */
    protected int $maxSegments = 6;

    /**
     * @return array<Segment>
     */
    abstract protected function getSegments(): array;

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    protected function getColor(): string
    {
        return $this->color;
    }

    /** The word under the total, in the middle of the ring. */
    protected function getTotalLabel(): string
    {
        return __('filament-appearance::appearance.breakdown.total');
    }

    /** The label of the segment the smallest ones are folded into. */
    protected function getOthersLabel(): string
    {
        return __('filament-appearance::appearance.breakdown.others');
    }

    protected function getEmptyStateLabel(): string
    {
        return __('filament-appearance::appearance.breakdown.empty');
    }

    /** How an amount is written, in the legend and in the middle of the ring. */
    protected function formatValue(int|float $value): string
    {
        return (string) Number::format($value, locale: app()->getLocale());
    }

    /** While a lazy widget loads: its heading over the outline of a ring and a few legend lines. */
    public function placeholder(): View
    {
        return view('filament-appearance::widgets.breakdown-chart-widget-placeholder', [
            ...$this->getPlaceholderData(),
            'heading' => $this->getHeading(),
            'description' => $this->getDescription(),
            'lines' => min(3, max(2, $this->maxSegments)),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $segments = $this->foldedSegments();
        $total = array_sum(array_map(fn (Segment $segment): int|float => $segment->getValue(), $segments));

        return [
            'heading' => $this->getHeading(),
            'description' => $this->getDescription(),
            'total' => $this->formatValue($total),
            'totalLabel' => $this->getTotalLabel(),
            'emptyStateLabel' => $this->getEmptyStateLabel(),
            'segments' => $this->arcs($segments, $total),
        ];
    }

    /**
     * Only what is above zero, largest first; past the limit, the smallest become one segment.
     *
     * @return list<Segment>
     */
    private function foldedSegments(): array
    {
        $segments = array_values(array_filter($this->getSegments(), fn (Segment $segment): bool => $segment->getValue() > 0));
        usort($segments, fn (Segment $first, Segment $second): int => $second->getValue() <=> $first->getValue());

        $max = max(2, min(6, $this->maxSegments));

        if (count($segments) <= $max) {
            return $segments;
        }

        $others = array_sum(array_map(fn (Segment $segment): int|float => $segment->getValue(), array_slice($segments, $max - 1)));

        return [...array_slice($segments, 0, $max - 1), Segment::make($this->getOthersLabel(), $others)->color('gray')];
    }

    /**
     * The ring is a circle of circumference 100, so a share is its own arc length.
     *
     * @param  list<Segment>  $segments
     * @return list<array{label: string, value: string, percent: int, tooltip: string, anchor: array{x: float, y: float}, share: float, offset: float, color: string}>
     */
    private function arcs(array $segments, int|float $total): array
    {
        $ramp = self::RAMPS[count($segments)] ?? [];
        $arcs = [];
        $start = 0.0;

        foreach ($segments as $index => $segment) {
            $share = $total > 0 ? $segment->getValue() / $total * 100 : 0.0;
            $color = $this->cssColor($segment->getColor(), $ramp[$index] ?? 500);

            $arcs[] = [
                'label' => $segment->getLabel(),
                'value' => $this->formatValue($segment->getValue()),
                'percent' => (int) round($share),
                'tooltip' => $this->tooltip($segment, $color, (int) round($share)),
                'anchor' => $this->anchor($start + $share / 2),
                'share' => round($share, 3),
                'offset' => round(25 - $start, 3),
                'color' => $color,
            ];

            $start += $share;
        }

        return $arcs;
    }

    /**
     * What shows on hover, laid out like the tooltip of Filament's chart widgets: the label, then
     * a line with the segment's colour, its amount and its share.
     */
    private function tooltip(Segment $segment, string $color, int $percent): string
    {
        return '<div class="fap-chart-tooltip">'
            .'<strong>'.e($segment->getLabel()).'</strong>'
            .'<span><i style="background-color: '.e($color).'"></i>'.e($this->formatValue($segment->getValue())).' ('.$percent.'%)</span>'
            .'</div>';
    }

    /**
     * The middle of a segment's arc, as fractions of the chart's box: where its tooltip points.
     * The ring starts at the top and runs clockwise.
     *
     * @return array{x: float, y: float}
     */
    private function anchor(float $position): array
    {
        $angle = $position / 100 * 2 * M_PI;
        $radius = 15.9155 / 42;

        return ['x' => round(0.5 + $radius * sin($angle), 4), 'y' => round(0.5 - $radius * cos($angle), 4)];
    }

    /** A segment's own colour, or its shade of the widget's colour. */
    private function cssColor(?string $color, int $shade): string
    {
        if ($color === null) {
            return "var(--{$this->getColor()}-{$shade})";
        }

        return array_key_exists($color, FilamentColor::getColors()) ? "var(--{$color}-400)" : $color;
    }
}
