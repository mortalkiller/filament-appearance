<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use Livewire\Livewire;
use MortalKiller\FilamentAppearance\Tests\Fixtures\SalesByChannel;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class BreakdownChartWidgetTest extends TestCase
{
    public function test_it_shows_the_total_and_each_part_with_its_amount_and_share_largest_first(): void
    {
        Livewire::test(SalesByChannel::class)
            ->assertSeeInOrder(['Sales by channel', 'This year.', '3,250', 'total', 'Online', '2,400', '74%', 'Shop', '850', '26%'])
            ->assertDontSee('Returns')
            ->assertSeeHtml('aria-label="Online 74%, Shop 26%"');
    }

    public function test_the_ring_is_drawn_in_shades_of_one_filament_colour(): void
    {
        Livewire::test(SalesByChannel::class, ['ramp' => 'success'])
            ->assertSeeHtml('stroke="var(--success-700)"')
            ->assertSeeHtml('stroke="var(--success-400)"')
            ->assertSeeHtml('stroke-dasharray="73.846 26.154"')
            ->assertSeeHtml('stroke-dashoffset="-48.846"');
    }

    public function test_a_segment_can_have_a_colour_of_its_own(): void
    {
        Livewire::test(SalesByChannel::class, ['firstColor' => 'danger'])->assertSeeHtml('stroke="var(--danger-400)"');
        Livewire::test(SalesByChannel::class, ['firstColor' => '#123456'])->assertSeeHtml('stroke="#123456"');
    }

    public function test_past_the_limit_the_smallest_parts_become_one(): void
    {
        Livewire::test(SalesByChannel::class, ['amounts' => ['A' => 50, 'B' => 30, 'C' => 15, 'D' => 5], 'limit' => 3])
            ->assertSeeInOrder(['A', '50%', 'B', '30%', 'Others', '20', '20%'])
            ->assertDontSeeHtml('C</dt>')
            ->assertSeeHtml('stroke="var(--gray-400)"');
    }

    public function test_hovering_a_segment_or_its_legend_line_shows_its_label_amount_and_share(): void
    {
        $tooltips = $this->tooltipsOf(Livewire::test(SalesByChannel::class)->html());

        // One on each segment and one on each legend line, pointing at the same arc.
        self::assertCount(4, $tooltips);
        self::assertSame($tooltips[0], $tooltips[2]);
        self::assertStringContainsString('<strong>Online</strong>', $tooltips[0]);
        self::assertStringContainsString('2,400 (74%)', $tooltips[0]);
        self::assertStringContainsString('background-color: var(--primary-700)', $tooltips[0]);
        self::assertStringContainsString('<strong>Shop</strong>', $tooltips[1]);
    }

    public function test_a_label_cannot_bring_markup_into_its_tooltip(): void
    {
        $tooltips = $this->tooltipsOf(Livewire::test(SalesByChannel::class, ['amounts' => ['<img src=x>' => 10]])->html());

        self::assertStringContainsString('<strong>&lt;img src=x&gt;</strong>', $tooltips[0]);
    }

    /**
     * The HTML each tooltip is given, as the browser would read it from the attribute.
     *
     * @return list<string>
     */
    private function tooltipsOf(string $html): array
    {
        preg_match_all("/content: '(.*?)',/s", $html, $matches);

        return array_map(fn (string $literal): string => (string) json_decode('"'.$literal.'"'), $matches[1]);
    }

    public function test_a_lazy_widget_loads_behind_a_skeleton_shaped_like_the_chart(): void
    {
        $html = app(SalesByChannel::class)->placeholder()->render();

        self::assertStringContainsString('Sales by channel', $html);
        self::assertStringContainsString('This year.', $html);
        self::assertStringContainsString('fap-breakdown-skeleton-ring', $html);
        self::assertSame(6, substr_count($html, 'fap-breakdown-skeleton-bone'));
        self::assertStringContainsString('aria-busy="true"', $html);
        self::assertStringNotContainsString('<svg', $html);
    }

    public function test_it_says_so_when_there_is_nothing_to_show(): void
    {
        Livewire::test(SalesByChannel::class, ['amounts' => ['Shop' => 0]])
            ->assertSee('Nothing to show for this period.')
            ->assertDontSeeHtml('<svg');
    }
}
