<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use Livewire\Livewire;
use MortalKiller\FilamentAppearance\Livewire\PalettePicker;
use MortalKiller\FilamentAppearance\Models\CustomPalette;
use MortalKiller\FilamentAppearance\Tests\Fixtures\User;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class PlainPanelTest extends TestCase
{
    protected string $panel = 'plain';

    public function test_a_panel_with_palettes_only_has_no_shell_features(): void
    {
        $this->signIn();

        $this->get('/plain')
            ->assertOk()
            ->assertSee('--fap-action: #f59e0b', escape: false)
            ->assertSee('<meta name="filament-appearance" content="">', escape: false)
            ->assertDontSee('fap-tab-bar', escape: false)
            ->assertDontSee('fap-collapsed-logo', escape: false)
            ->assertDontSee('cell.dataset.label', escape: false);
    }

    public function test_custom_palettes_are_off_unless_the_plugin_allows_them(): void
    {
        $user = User::factory()->create();
        $palette = CustomPalette::factory()->for($user, 'user')->create();

        self::assertFalse($user->usePalette($palette));

        $this->signIn($user);

        Livewire::test(PalettePicker::class)
            ->assertDontSee('Your palettes')
            ->assertActionHidden('createPalette');
    }
}
