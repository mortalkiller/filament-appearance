<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use Livewire\Livewire;
use MortalKiller\FilamentAppearance\Livewire\PalettePicker;
use MortalKiller\FilamentAppearance\Models\CustomPalette;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class PalettePickerTest extends TestCase
{
    public function test_picking_a_palette_saves_it_on_the_user(): void
    {
        $user = $this->signIn();

        Livewire::test(PalettePicker::class)->call('selectPalette', 'ink');

        self::assertSame('ink', $user->refresh()->getPaletteKey());
    }

    public function test_a_palette_that_does_not_exist_is_ignored(): void
    {
        $user = $this->signIn();

        Livewire::test(PalettePicker::class)->call('selectPalette', 'neon');

        self::assertNull($user->refresh()->getPaletteKey());
    }

    public function test_a_user_makes_a_palette_from_three_colours_and_starts_using_it(): void
    {
        $user = $this->signIn();

        Livewire::test(PalettePicker::class)->callAction('createPalette', [
            'name' => 'Wine',
            'primary_color' => '#9f1239',
            'navigation_color' => '#3b0d1a',
            'background_color' => '#faf5f5',
        ]);

        $palette = $user->customPalettes()->sole();

        self::assertSame('Wine', $palette->name);
        self::assertSame($palette->key(), $user->refresh()->getPaletteKey());
    }

    public function test_a_colour_that_is_not_a_hex_code_is_refused(): void
    {
        $user = $this->signIn();

        Livewire::test(PalettePicker::class)
            ->callAction('createPalette', [
                'name' => 'Broken',
                'primary_color' => 'red; } body { display: none',
                'navigation_color' => '#3b0d1a',
                'background_color' => '#faf5f5',
            ])
            ->assertHasActionErrors(['primary_color']);

        self::assertSame(0, $user->customPalettes()->count());
    }

    public function test_new_palettes_stop_being_offered_at_the_limit(): void
    {
        $user = $this->signIn();
        CustomPalette::factory()->for($user, 'user')->count(2)->create();

        Livewire::test(PalettePicker::class)->assertActionHidden('createPalette');
    }

    public function test_a_user_cannot_edit_someone_elses_palette(): void
    {
        $this->signIn();
        $foreign = CustomPalette::factory()->create();

        Livewire::test(PalettePicker::class)
            ->call('mountAction', 'editPalette', ['palette' => $foreign->getKey()])
            ->assertNotFound();
    }

    public function test_deleting_the_palette_in_use_goes_back_to_the_default_one(): void
    {
        $user = $this->signIn();
        $palette = CustomPalette::factory()->for($user, 'user')->create();
        $user->usePalette($palette);

        Livewire::test(PalettePicker::class)->callAction('deletePalette', arguments: ['palette' => $palette->getKey()]);

        self::assertNull($user->refresh()->getPaletteKey());
        self::assertSame(0, CustomPalette::query()->count());
    }

    public function test_guests_cannot_use_the_picker(): void
    {
        Livewire::test(PalettePicker::class)->assertForbidden();
    }
}
