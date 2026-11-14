<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Support;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Js;
use Illuminate\View\ComponentAttributeBag;

/**
 * A small bar chart for a stat: one bar per value, as tall as the value relative to the largest,
 * the last one (usually the current month) in full colour and the others lighter.
 *
 *     Stat::make('Paid in November', '€20.00')->bars([55, 80, 40, 100, 65, 12], 'warning');
 *
 * With labels, hovering a bar's column shows what it stands for, in the chart tooltip style.
 */
final class BarSparkline implements Htmlable
{
    private string $color = 'primary';

    private ?string $label = null;

    /** @var list<string> */
    private array $labels = [];

    private ?Closure $formatValueUsing = null;

    /** @param  list<int|float>  $values */
    private function __construct(private readonly array $values) {}

    /** @param  array<int|float>  $values */
    public static function make(array $values): self
    {
        return new self(array_values($values));
    }

    /** Any Filament colour registered on the panel (primary, warning, a custom one…). */
    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /** What the bars show, for screen readers. */
    public function label(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * One label per bar ("June"): with it, hovering the bar's column shows a tooltip like the
     * ones of Filament's chart widgets, the label and the value in the bar's colour.
     *
     * @param  array<string>  $labels
     */
    public function labels(array $labels): self
    {
        $this->labels = array_values($labels);

        return $this;
    }

    /** How the value reads in the tooltip ("€55.00"); the plain number when not set. */
    public function formatValueUsing(?Closure $callback): self
    {
        $this->formatValueUsing = $callback;

        return $this;
    }

    /** @return list<array{height: float, isCurrent: bool, label: ?string, value: string}> */
    public function getBars(): array
    {
        $largest = max([0, ...$this->values]);
        $last = array_key_last($this->values);

        return array_map(fn (int $index, int|float $value): array => [
            'height' => $largest > 0 ? round(max(0, $value) / $largest * 100, 1) : 0.0,
            'isCurrent' => $index === $last,
            'label' => $this->labels[$index] ?? null,
            'value' => (string) ($this->formatValueUsing === null ? $value : ($this->formatValueUsing)($value)),
        ], array_keys($this->values), $this->values);
    }

    public function toHtml(): string
    {
        // Only a colour name goes into the style attributes.
        $color = preg_replace('/[^a-z0-9-]/', '', strtolower($this->color)) ?: 'primary';

        return view('filament-appearance::components.bar-sparkline', [
            'attributes' => new ComponentAttributeBag(array_filter([
                'class' => 'fap-bar-sparkline',
                'style' => "--fap-sparkline-color: var(--{$color}-500)",
                'role' => $this->label === null ? null : 'img',
                'aria-label' => $this->label,
            ])),
            'columns' => array_map(fn (array $bar): array => [
                'height' => $bar['height'],
                'isCurrent' => $bar['isCurrent'],
                'attributes' => new ComponentAttributeBag(array_filter([
                    'class' => 'fap-bar-sparkline-column',
                    'x-tooltip' => $bar['label'] === null ? null : self::tooltip($bar['label'], $bar['value'], $color),
                ])),
            ], $this->getBars()),
        ])->render();
    }

    /**
     * The Alpine expression of a column's tooltip, laid out like the tooltips of Filament's chart
     * widgets: the label in bold, then the value beside a square in the bars' colour.
     */
    private static function tooltip(string $label, string $value, string $color): string
    {
        $content = '<div class="fap-chart-tooltip"><strong>'.e($label).'</strong>'
            .'<span><i style="background-color: var(--'.$color.'-500)"></i>'.e($value).'</span></div>';

        return '{ content: '.Js::from($content).", allowHTML: true, theme: 'fap-chart', placement: 'top' }";
    }
}
