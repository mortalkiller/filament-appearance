<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Support;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Number;

/**
 * A thin progress bar, for a stat description, a table cell or any view.
 *
 *     Stat::make('Approved', '3 of 5')->description(ProgressBar::make(60));
 */
final class ProgressBar implements Htmlable
{
    private string $color = 'primary';

    private bool $hasValue = false;

    private int $precision = 0;

    private function __construct(private readonly int|float|null $percent) {}

    /** @param  int|float|null  $percent  Null draws an empty bar: there is nothing to compare. */
    public static function make(int|float|null $percent): self
    {
        return new self($percent);
    }

    /** A Filament colour name: primary, success, warning, danger, info or gray. */
    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /** Shows the percentage beside the bar. */
    public function value(bool $condition = true, int $precision = 0): self
    {
        $this->hasValue = $condition;
        $this->precision = $precision;

        return $this;
    }

    public function getFormattedValue(): string
    {
        if ($this->percent === null) {
            return '—';
        }

        return Number::format($this->percent, precision: $this->precision, locale: app()->getLocale()).'%';
    }

    public function toHtml(): string
    {
        return view('filament-appearance::components.progress-bar', [
            'width' => $this->percent === null ? 0 : min(100, max(0, $this->percent)),
            'color' => $this->percent === null ? 'gray' : $this->color,
            'value' => $this->hasValue ? $this->getFormattedValue() : null,
            'isNegative' => $this->percent !== null && $this->percent < 0,
        ])->render();
    }
}
