<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Widgets\BreakdownChartWidget;

/**
 * One slice of a BreakdownChartWidget: a label and an amount.
 */
final class Segment
{
    private ?string $color = null;

    private function __construct(private readonly string $label, private readonly int|float $value) {}

    public static function make(string $label, int|float $value): self
    {
        return new self($label, $value);
    }

    /**
     * A colour of its own instead of its place in the widget's ramp: a Filament colour name
     * ("danger", "gray") or any CSS colour.
     */
    public function color(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getValue(): int|float
    {
        return $this->value;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
}
