<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use MortalKiller\FilamentAppearance\Facades\Appearance;
use MortalKiller\FilamentAppearance\Models\CustomPalette;
use MortalKiller\FilamentAppearance\Tests\Fixtures\User;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class HasPaletteTest extends TestCase
{
    public function test_a_user_sees_the_default_palette_until_they_pick_another(): void
    {
        $user = User::factory()->create();

        self::assertSame('forest', $user->currentPalette()->getId());
        self::assertTrue($user->usePalette('ink'));
        self::assertSame('ink', $user->refresh()->getPaletteKey());
        self::assertSame('ink', $user->currentPalette()->getId());
    }

    public function test_the_default_palette_is_stored_as_no_choice(): void
    {
        $user = User::factory()->create(['palette' => 'ink']);

        $user->usePalette(Appearance::defaultPalette());

        self::assertNull($user->refresh()->getPaletteKey());
    }

    public function test_a_palette_that_does_not_exist_is_refused(): void
    {
        $user = User::factory()->create();

        self::assertFalse($user->usePalette('neon'));
        self::assertNull($user->refresh()->getPaletteKey());
    }

    public function test_a_user_can_use_their_own_palette_but_not_someone_elses(): void
    {
        $user = User::factory()->create();
        $own = CustomPalette::factory()->for($user, 'user')->create(['name' => 'Wine', 'navigation_color' => '#3b0d1a']);
        $foreign = CustomPalette::factory()->create();

        self::assertTrue($user->usePalette($own));
        self::assertSame('Wine', $user->currentPalette()->getLabel());
        self::assertTrue($user->currentPalette()->isCustom());

        self::assertFalse($user->usePalette($foreign));

        $user->forceFill(['palette' => $foreign->key()])->save();

        self::assertSame('forest', $user->currentPalette()->getId());
    }

    public function test_a_choice_that_is_gone_falls_back_to_the_default_palette(): void
    {
        $user = User::factory()->create(['palette' => 'retired']);

        self::assertSame('forest', $user->currentPalette()->getId());
        self::assertSame('forest', Appearance::paletteFor(null)->getId());
    }
}
