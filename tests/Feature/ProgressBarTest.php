<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use Filament\Widgets\StatsOverviewWidget\Stat;
use MortalKiller\FilamentAppearance\Support\BarList;
use MortalKiller\FilamentAppearance\Support\BarSparkline;
use MortalKiller\FilamentAppearance\Support\ProgressBar;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class ProgressBarTest extends TestCase
{
    public function test_it_draws_a_bar_as_wide_as_the_percentage(): void
    {
        $html = ProgressBar::make(60)->toHtml();

        self::assertStringContainsString('class="fap-progress"', $html);
        self::assertStringContainsString('data-color="primary"', $html);
        self::assertStringContainsString('width: 60%', $html);
        self::assertStringNotContainsString('fap-progress-value', $html);
    }

    public function test_the_bar_stays_inside_its_track(): void
    {
        self::assertStringContainsString('width: 100%', ProgressBar::make(140)->toHtml());
        self::assertStringContainsString('width: 0%', ProgressBar::make(-12.5)->toHtml());
    }

    public function test_it_can_show_the_percentage_beside_the_bar(): void
    {
        $html = ProgressBar::make(-12.5)->color('danger')->value(precision: 1)->toHtml();

        self::assertStringContainsString('data-color="danger"', $html);
        self::assertStringContainsString('-12.5%', $html);
        self::assertStringContainsString('fap-progress-value-negative', $html);
    }

    public function test_nothing_to_compare_is_an_empty_grey_bar(): void
    {
        $html = ProgressBar::make(null)->color('warning')->value()->toHtml();

        self::assertStringContainsString('data-color="gray"', $html);
        self::assertStringContainsString('—', $html);
    }

    public function test_a_stat_can_show_progress_as_its_description(): void
    {
        $description = Stat::make('Approved', '3 of 5')->progress(60, 'success')->getDescription();

        self::assertInstanceOf(ProgressBar::class, $description);
        self::assertStringContainsString('data-color="success"', $description->toHtml());
    }

    public function test_a_stat_can_put_its_figure_in_a_status_colour(): void
    {
        $stat = Stat::make('Unpaid', '€61.50')->extraAttributes(['class' => 'own'])->valueColor('warning');

        self::assertEqualsCanonicalizing(['class' => 'own', 'data-fap-value-color' => 'warning'], $stat->getExtraAttributes());
    }

    public function test_a_bar_sparkline_draws_each_value_against_the_largest_and_marks_the_last(): void
    {
        $html = BarSparkline::make([50, 100, 0, 25])->color('clay')->label('Paid per month')->toHtml();

        self::assertSame(
            [[50.0, false], [100.0, false], [0.0, false], [25.0, true]],
            array_map(fn (array $bar): array => [$bar['height'], $bar['isCurrent']], BarSparkline::make([50, 100, 0, 25])->getBars()),
        );
        self::assertSame(
            [['June', '€50'], [null, '€100']],
            array_map(fn (array $bar): array => [$bar['label'], $bar['value']], BarSparkline::make([50, 100])->labels(['June'])->formatValueUsing(fn (int $value): string => '€'.$value)->getBars()),
        );
        self::assertStringContainsString('var(--clay-500)', $html);
        self::assertStringContainsString('aria-label="Paid per month"', $html);
        self::assertStringContainsString('fap-bar-sparkline-current', $html);
        self::assertStringContainsString('fap-chart', BarSparkline::make([1, 2])->labels(['June', 'July'])->toHtml());
    }

    public function test_a_sparkline_colour_cannot_break_out_of_its_style_attribute(): void
    {
        self::assertStringContainsString('var(--redbodycolorred-500)', BarSparkline::make([1])->color('red;}body{color:red')->toHtml());
    }

    public function test_a_stat_can_show_bars_as_its_description(): void
    {
        self::assertInstanceOf(BarSparkline::class, Stat::make('Paid', '€20.00')->bars([1, 2, 3])->getDescription());
    }

    public function test_a_bar_list_measures_each_bar_against_the_largest(): void
    {
        $html = BarList::make()
            ->item('Cloud Demo', '52%', 20000)
            ->item('Loja Demo', '€61.50', 5000, note: '€61.50 overdue', noteColor: 'danger')
            ->color('warning')
            ->toHtml();

        self::assertStringContainsString('--fap-progress-color: var(--warning-500)', $html);
        self::assertStringContainsString('width: 100%', $html);
        self::assertStringContainsString('width: 25%', $html);
        self::assertStringContainsString('--fap-bar-list-note-color: var(--danger-700)', $html);
        self::assertStringContainsString('€61.50 overdue', $html);
        self::assertStringNotContainsString('fap-bar-list-strong', $html);
    }

    public function test_a_bar_list_without_amounts_draws_empty_bars(): void
    {
        $list = BarList::make()->item('Nothing', '0%', 0)->strongValues();

        self::assertFalse($list->isEmpty());
        self::assertStringContainsString('width: 0%', $list->toHtml());
        self::assertStringContainsString('fap-bar-list-strong', $list->toHtml());
        self::assertTrue(BarList::make()->isEmpty());
    }
}
