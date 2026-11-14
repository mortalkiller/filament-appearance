<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Fixtures;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use MortalKiller\FilamentAppearance\Widgets\Concerns\GroupsStats;

final class GroupedStats extends StatsOverviewWidget
{
    use GroupsStats;

    protected function getStats(): array
    {
        return [Stat::make('Orders', '12'), Stat::make('Revenue', '3,400')];
    }
}
