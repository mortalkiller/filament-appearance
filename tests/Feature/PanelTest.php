<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use MortalKiller\FilamentAppearance\Models\CustomPalette;
use MortalKiller\FilamentAppearance\Tests\Fixtures\User;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class PanelTest extends TestCase
{
    public function test_the_panel_is_painted_with_the_default_palette(): void
    {
        $this->signIn();

        $this->get('/test')
            ->assertOk()
            ->assertSee('--fap-navigation: #0a5b42', escape: false)
            ->assertSee('--fap-background: #e5f3f1', escape: false)
            ->assertSee('<meta name="filament-appearance" content="search tabs tables edge tabbar">', escape: false);
    }

    public function test_the_panel_is_painted_with_the_palette_the_user_picked(): void
    {
        $this->signIn(User::factory()->create(['palette' => 'paper']));

        $this->get('/test')
            ->assertOk()
            ->assertSee('--fap-navigation: #ffffff', escape: false)
            ->assertSee('--fap-navigation-text: #111111', escape: false)
            ->assertSee('--fap-action: #2563eb', escape: false);
    }

    public function test_the_panel_is_painted_with_a_palette_the_user_made(): void
    {
        $user = User::factory()->create();
        $palette = CustomPalette::factory()->for($user, 'user')->create(['navigation_color' => '#3b0d1a']);
        $user->usePalette($palette);

        $this->signIn($user);

        $this->get('/test')->assertOk()->assertSee('--fap-navigation: #3b0d1a', escape: false);
    }

    public function test_a_palette_brings_its_own_logo(): void
    {
        $this->signIn();

        $this->get('/test')
            ->assertOk()
            ->assertSee('/logos/forest.svg', escape: false)
            ->assertDontSee('/logos/on-light.svg', escape: false);
    }

    public function test_a_palette_can_have_one_logo_for_each_mode(): void
    {
        $this->signIn(User::factory()->create(['palette' => 'ink']));

        $this->get('/test')
            ->assertOk()
            ->assertSee('/logos/ink-light.svg', escape: false)
            ->assertSee('/logos/ink-dark.svg', escape: false);
    }

    public function test_a_palette_without_a_logo_gets_the_panel_logo_that_reads_on_its_navigation_bar(): void
    {
        $this->signIn(User::factory()->create(['palette' => 'paper']));

        // Light bar in light mode, dark bar in dark mode.
        $this->get('/test')
            ->assertOk()
            ->assertSee('/logos/on-light.svg', escape: false)
            ->assertSee('/logos/on-dark.svg', escape: false)
            ->assertDontSee('/logos/forest.svg', escape: false);
    }

    public function test_a_collapsed_sidebar_shows_the_compact_logo_that_reads_on_the_navigation_bar(): void
    {
        $this->signIn(User::factory()->create(['palette' => 'paper']));

        // Light bar in light mode, dark bar in dark mode.
        $this->get('/test')
            ->assertOk()
            ->assertSee('class="fap-collapsed-logo"', escape: false)
            ->assertSee('<img src="/logos/symbol-on-light.svg" alt="" class="fap-collapsed-logo-image fap-collapsed-logo-light">', escape: false)
            ->assertSee('<img src="/logos/symbol-on-dark.svg" alt="" class="fap-collapsed-logo-image fap-collapsed-logo-dark">', escape: false);
    }

    public function test_the_page_knows_before_alpine_starts_whether_the_sidebar_was_left_collapsed(): void
    {
        $this->signIn();

        $this->get('/test')
            ->assertOk()
            ->assertSee("window.localStorage.getItem('filament:sidebar:open') === 'false'", escape: false)
            ->assertSee('fap-sidebar-collapsed', escape: false);
    }

    public function test_a_palette_can_bring_its_own_compact_logo(): void
    {
        $this->signIn(User::factory()->create(['palette' => 'ink']));

        $this->get('/test')
            ->assertOk()
            ->assertSee('/logos/ink-symbol.svg', escape: false)
            ->assertDontSee('/logos/symbol-on-dark.svg', escape: false);
    }

    public function test_the_sign_in_page_uses_the_panel_logo_and_the_default_palette(): void
    {
        $this->get('/test/login')
            ->assertOk()
            ->assertSee('--fap-navigation: #0a5b42', escape: false)
            ->assertSee('/logos/on-light.svg', escape: false)
            ->assertDontSee('/logos/forest.svg', escape: false)
            ->assertDontSee('fap-tab-bar', escape: false)
            ->assertDontSee('tabbar', escape: false);
    }

    public function test_phones_get_a_tab_bar_and_a_sheet_with_the_rest_of_the_menu(): void
    {
        $this->signIn();

        $this->get('/test')
            ->assertOk()
            ->assertSee('fap-tab-bar', escape: false)
            ->assertSee('--fap-tab-bar-columns: 3', escape: false)
            ->assertSee('<button class="new-record">New</button>', escape: false)
            ->assertSee('class="fap-menu-handle"', escape: false)
            ->assertSee('x-on:pointerdown="startPull($event)"', escape: false)
            ->assertSee('fap-menu-tile', escape: false)
            ->assertSee('fap-menu-row', escape: false)
            // No searchable resource on this panel: the sheet has its plain filter of the pages.
            ->assertSee('class="fap-menu-search"', escape: false)
            ->assertDontSee('fap-menu-global-search', escape: false)
            ->assertSee('Reports')
            ->assertSee('cell.dataset.label', escape: false);
    }

    public function test_the_appearance_page_shows_the_picker(): void
    {
        $this->signIn();

        $this->get('/test/appearance')
            ->assertOk()
            ->assertSee('Colour palette')
            ->assertSee('Forest')
            ->assertSee('In use')
            ->assertSee('Paper')
            ->assertSee('Your palettes');
    }
}
