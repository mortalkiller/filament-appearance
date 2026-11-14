<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Unit;

use InvalidArgumentException;
use MortalKiller\FilamentAppearance\Palette;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaletteTest extends TestCase
{
    public function test_a_palette_needs_only_a_primary_colour(): void
    {
        $palette = Palette::make('deep-sea')->primary('#2563EB');

        self::assertSame('deep-sea', $palette->getId());
        self::assertSame('Deep Sea', $palette->getLabel());
        self::assertSame('#2563eb', $palette->getPrimary());
        self::assertSame('#2563eb', $palette->getAction());
        self::assertSame('#2563eb', $palette->getAction(dark: true));
        self::assertSame('#2563eb', $palette->getNavigation());
        self::assertSame('#2563eb', $palette->getNavigation(dark: true));
        self::assertNull($palette->getBackground());
        self::assertFalse($palette->isCustom());
    }

    public function test_a_light_navigation_bar_turns_near_black_in_dark_mode(): void
    {
        $palette = Palette::make('paper')->primary('#2563eb')->navigation('#ffffff');

        self::assertTrue($palette->hasLightNavigation());
        self::assertSame('#171717', $palette->getNavigation(dark: true));
        self::assertFalse($palette->hasLightNavigation(dark: true));
    }

    public function test_dark_mode_colours_can_be_set_apart(): void
    {
        $palette = Palette::make('night')
            ->primary('#3fa66b')
            ->action('#12372a')
            ->navigation('#e8f7ef')
            ->dark(action: '#e8f7ef', navigation: '#12372a');

        self::assertSame('#12372a', $palette->getAction());
        self::assertSame('#e8f7ef', $palette->getAction(dark: true));
        self::assertSame('#e8f7ef', $palette->getNavigation());
        self::assertSame('#12372a', $palette->getNavigation(dark: true));
    }

    public function test_css_variables_follow_the_navigation_colour(): void
    {
        $palette = Palette::make('paper')
            ->primary('#2563eb')
            ->navigation('#ffffff', dark: '#111827')
            ->background('#f3f4f6');

        $light = $palette->cssVariables();
        $dark = $palette->cssVariables(dark: true);

        self::assertSame('#ffffff', $light['--fap-navigation']);
        self::assertSame('#111111', $light['--fap-navigation-text']);
        self::assertSame('rgb(17 17 17 / 0.08)', $light['--fap-navigation-hover']);
        self::assertSame('#f3f4f6', $light['--fap-background']);
        self::assertSame('#ffffff', $light['--fap-on-action']);

        self::assertSame('#111827', $dark['--fap-navigation']);
        self::assertSame('#ffffff', $dark['--fap-navigation-text']);
        self::assertSame('rgb(255 255 255 / 0.08)', $dark['--fap-navigation-hover']);
        self::assertArrayNotHasKey('--fap-background', $dark);
    }

    public function test_the_navigation_can_glow_in_both_modes(): void
    {
        $palette = Palette::make('night')->primary('#5a3df0')->navigation('#0c0e1d')->navigationGlow('#19F5A0', '#5a3df0');

        self::assertSame(['#19f5a0', '#5a3df0'], $palette->getNavigationGlow());
        self::assertSame(
            'radial-gradient(130% 38% at 100% 64%, #5a3df099, transparent 70%), radial-gradient(150% 34% at 0% 100%, #19f5a073, transparent 72%)',
            $palette->cssVariables()['--fap-navigation-image'],
        );
        self::assertSame($palette->cssVariables()['--fap-navigation-image'], $palette->cssVariables(dark: true)['--fap-navigation-image']);
        self::assertSame(
            'radial-gradient(150% 34% at 0% 100%, #19f5a073, transparent 72%)',
            Palette::make('one')->primary('#5a3df0')->navigationGlow('#19f5a0')->cssVariables()['--fap-navigation-image'],
        );
        self::assertArrayNotHasKey('--fap-navigation-image', Palette::make('plain')->primary('#2563eb')->cssVariables());
    }

    public function test_a_glow_refuses_anything_that_is_not_a_hex_colour(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Palette::make('night')->primary('#5a3df0')->navigationGlow('red; background: url(x)');
    }

    public function test_a_palette_without_a_background_leaves_the_canvas_to_filament(): void
    {
        self::assertArrayNotHasKey('--fap-background', Palette::make('plain')->primary('#2563eb')->cssVariables());
    }

    public function test_filament_colours_use_hand_tuned_shades_when_given(): void
    {
        $generated = Palette::make('generated')->primary('#2563eb')->filamentColors();
        $tuned = Palette::make('tuned')
            ->primary('#2563eb')
            ->primaryShades([500 => '#123456'])
            ->grayShades([500 => '#777777'])
            ->filamentColors();

        self::assertArrayHasKey(500, $generated['primary']);
        self::assertArrayNotHasKey('gray', $generated);
        self::assertSame([500 => '#123456'], $tuned['primary']);
        self::assertSame([500 => '#777777'], $tuned['gray']);
    }

    public function test_a_palette_can_carry_one_logo_for_both_modes_or_one_for_each(): void
    {
        $same = Palette::make('same')->primary('#2563eb')->brandLogo('/logo.svg');
        $both = Palette::make('both')->primary('#2563eb')->brandLogo(fn (): string => '/light.svg', dark: '/dark.svg');
        $none = Palette::make('none')->primary('#2563eb');

        self::assertSame('/logo.svg', $same->getBrandLogo());
        self::assertSame('/logo.svg', $same->getBrandLogo(dark: true));
        self::assertSame('/light.svg', $both->getBrandLogo());
        self::assertSame('/dark.svg', $both->getBrandLogo(dark: true));
        self::assertFalse($none->hasBrandLogo());
        self::assertNull($none->getBrandLogo());
    }

    #[DataProvider('invalidColours')]
    public function test_it_refuses_anything_that_is_not_a_hex_colour(string $colour): void
    {
        $this->expectException(InvalidArgumentException::class);

        Palette::make('broken')->primary($colour);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidColours(): array
    {
        return [
            'a colour name' => ['red'],
            'a short hex code' => ['#fff'],
            'css that closes the rule' => ['#ffffff; } body { display: none'],
        ];
    }

    #[DataProvider('readableText')]
    public function test_it_picks_readable_text_for_a_background(string $background, string $text): void
    {
        self::assertSame($text, Palette::textOn($background));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function readableText(): array
    {
        return [
            'dark bar' => ['#111827', '#ffffff'],
            'white bar' => ['#ffffff', '#111111'],
            'mint bar' => ['#e8f7ef', '#111111'],
            'mid green' => ['#3fa66b', '#111111'],
        ];
    }
}
