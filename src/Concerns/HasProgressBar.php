<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Concerns;

use Closure;
use MortalKiller\FilamentAppearance\Support\ProgressBar;

/**
 * Shared by ProgressColumn and ProgressEntry: the state is a percentage, drawn as a bar.
 */
trait HasProgressBar
{
    protected string|Closure $progressColor = 'primary';

    protected int|Closure $progressPrecision = 1;

    protected bool|Closure $hasProgressValue = true;

    /** A Filament colour name, or a closure that picks one from the state. */
    public function color(string|Closure $color): static
    {
        $this->progressColor = $color;

        return $this;
    }

    /** Decimal places of the percentage shown beside the bar. */
    public function precision(int|Closure $precision): static
    {
        $this->progressPrecision = $precision;

        return $this;
    }

    /** Whether the percentage is shown beside the bar. */
    public function value(bool|Closure $condition = true): static
    {
        $this->hasProgressValue = $condition;

        return $this;
    }

    public function getProgressBar(): ProgressBar
    {
        $state = $this->getState();

        return ProgressBar::make(is_numeric($state) ? (float) $state : null)
            ->color((string) $this->evaluate($this->progressColor))
            ->value((bool) $this->evaluate($this->hasProgressValue), (int) $this->evaluate($this->progressPrecision));
    }
}
