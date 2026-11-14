<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Fixtures;

use MortalKiller\FilamentAppearance\Widgets\BreakdownChartWidget;
use MortalKiller\FilamentAppearance\Widgets\BreakdownChartWidget\Segment;

final class SalesByChannel extends BreakdownChartWidget
{
    protected ?string $heading = 'Sales by channel';

    protected ?string $description = 'This year.';

    /** @var array<string, int|float> */
    public array $amounts = ['Shop' => 850, 'Online' => 2400, 'Returns' => 0];

    public string $ramp = 'primary';

    public int $limit = 6;

    public ?string $firstColor = null;

    protected function getSegments(): array
    {
        $segments = [];

        foreach ($this->amounts as $label => $amount) {
            $segments[] = Segment::make($label, $amount)->color($segments === [] ? $this->firstColor : null);
        }

        return $segments;
    }

    protected function getColor(): string
    {
        return $this->ramp;
    }

    public function mount(): void
    {
        $this->maxSegments = $this->limit;
    }

    public function hydrate(): void
    {
        $this->maxSegments = $this->limit;
    }
}
