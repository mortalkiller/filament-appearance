<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use Livewire\Livewire;
use MortalKiller\FilamentAppearance\Tests\Fixtures\GroupedStats;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class GroupsStatsTest extends TestCase
{
    public function test_the_stats_of_the_widget_share_one_card(): void
    {
        $html = Livewire::test(GroupedStats::class)
            ->assertSeeInOrder(['Orders', '12', 'Revenue', '3,400'])
            ->html();

        // Filament puts a section's extra attributes on its wrapper, around the <section>.
        self::assertMatchesRegularExpression('/class="fap-stats-grouped fi-sc-section">\s*<section/', $html);
        self::assertSame(2, substr_count($html, 'fi-wi-stats-overview-stat"'));
    }
}
