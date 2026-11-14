<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Widgets\Concerns;

use Filament\Schemas\Components\Component;

/**
 * For a StatsOverviewWidget: its stats share one card, side by side, instead of a card each.
 * Filament has no option for this; the widget's section gets a class the package stylesheet
 * draws as the card (resources/css/stats.css).
 *
 *     class OrderSummary extends StatsOverviewWidget
 *     {
 *         use GroupsStats;
 *     }
 */
trait GroupsStats
{
    public function getSectionContentComponent(): Component
    {
        return parent::getSectionContentComponent()
            ->extraAttributes(['class' => 'fap-stats-grouped'], merge: true);
    }
}
