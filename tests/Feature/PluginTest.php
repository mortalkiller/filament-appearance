<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use InvalidArgumentException;
use MortalKiller\FilamentAppearance\AppearancePlugin;
use MortalKiller\FilamentAppearance\Facades\Appearance;
use MortalKiller\FilamentAppearance\Palette;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class PluginTest extends TestCase
{
    public function test_it_is_the_plugin_of_the_current_panel(): void
    {
        self::assertSame('filament-appearance', AppearancePlugin::make()->getId());
        self::assertSame(AppearancePlugin::get(), Appearance::plugin());
    }

    public function test_palettes_are_kept_by_id_in_the_order_given(): void
    {
        self::assertSame(['forest', 'paper', 'ink'], array_keys(Appearance::palettes()));
        self::assertSame('forest', Appearance::defaultPalette()->getId());
    }

    public function test_the_first_palette_is_the_default_one_unless_told_otherwise(): void
    {
        $plugin = AppearancePlugin::make()->palettes(fn (): array => [
            Palette::make('one')->primary('#2563eb'),
            Palette::make('two')->primary('#e11d48'),
        ]);

        self::assertSame('one', $plugin->getDefaultPalette()->getId());
        self::assertSame('two', $plugin->defaultPalette('two')->getDefaultPalette()->getId());
    }

    public function test_a_default_palette_that_is_not_registered_fails_clearly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[missing]');

        AppearancePlugin::make()
            ->palettes([Palette::make('one')->primary('#2563eb')])
            ->defaultPalette('missing')
            ->getDefaultPalette();
    }

    public function test_shell_features_are_off_until_asked_for(): void
    {
        $plugin = AppearancePlugin::make();

        self::assertSame([], $plugin->getFeatures());
        self::assertFalse($plugin->hasMobileTabBar());
        self::assertFalse($plugin->hasCustomPalettes());
        self::assertFalse($plugin->hasAppearancePage());

        $plugin->underlinedTabs()->mobileTables(fn (): bool => true)->mobileEdgeToEdge(false);

        self::assertSame(['tabs', 'tables'], $plugin->getFeatures());
        self::assertSame(['search', 'tabs', 'tables'], $plugin->topbarSearchAtStart()->getFeatures());
        self::assertSame(['search', 'tablesearch', 'tabs', 'tables'], $plugin->tableSearchAtStart()->getFeatures());
    }
}
