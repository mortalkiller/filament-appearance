<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance;

use Closure;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * One colour palette for a panel: the brand colour, the main button, the navigation bar and the
 * page background, in light and dark mode. Status colours (success, warning, danger, info) are
 * not part of a palette: they are the same in all of them.
 */
final class Palette
{
    private const LIGHT_TEXT = '#ffffff';

    private const DARK_TEXT = '#111111';

    /** Navigation colour used in dark mode when the light-mode one is too light to keep. */
    private const DARK_NAVIGATION = '#171717';

    private const HEX_COLOR = '/^#[0-9a-fA-F]{6}$/';

    private ?string $label = null;

    private ?string $description = null;

    private string $primary = '#f59e0b';

    private ?string $action = null;

    private ?string $actionDark = null;

    private ?string $navigation = null;

    private ?string $navigationDark = null;

    private ?string $background = null;

    /** @var list<string> */
    private array $navigationGlow = [];

    /** @var array<int, string>|null */
    private ?array $primaryShades = null;

    /** @var array<int, string>|null */
    private ?array $grayShades = null;

    private string|Htmlable|Closure|null $brandLogo = null;

    private string|Htmlable|Closure|null $darkModeBrandLogo = null;

    private string|Htmlable|Closure|null $collapsedBrandLogo = null;

    private string|Htmlable|Closure|null $darkModeCollapsedBrandLogo = null;

    private ?string $brandLogoHeight = null;

    private bool $isCustom = false;

    private function __construct(private readonly string $id) {}

    public static function make(string $id): self
    {
        return new self($id);
    }

    public function label(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /** Brand colour: Filament's "primary" scale (links, focus rings, charts). */
    public function primary(string $color): self
    {
        $this->primary = self::hex($color);

        return $this;
    }

    /** Main button background. Defaults to the primary colour, in both modes. */
    public function action(string $color, ?string $dark = null): self
    {
        $this->action = self::hex($color);
        $this->actionDark = $dark === null ? $this->actionDark : self::hex($dark);

        return $this;
    }

    /**
     * Navigation bar background. Defaults to the action colour. In dark mode it keeps the same
     * colour, unless that colour is a light one: then it becomes near-black.
     */
    public function navigation(string $color, ?string $dark = null): self
    {
        $this->navigation = self::hex($color);
        $this->navigationDark = $dark === null ? $this->navigationDark : self::hex($dark);

        return $this;
    }

    /** Page background behind cards and tables, in light mode. Filament's own when not set. */
    public function background(string $color): self
    {
        $this->background = self::hex($color);

        return $this;
    }

    /**
     * Soft glows of colour over the navigation bar, in both modes: the first rises from the
     * bottom corner, the second (optional) from the far side halfway down. They stay clear of
     * the top, so the logo corner keeps the plain navigation colour.
     */
    public function navigationGlow(string $color, ?string $second = null): self
    {
        $this->navigationGlow = array_map(self::hex(...), array_values(array_filter([$color, $second])));

        return $this;
    }

    /** The colours that change in dark mode. */
    public function dark(?string $action = null, ?string $navigation = null): self
    {
        $this->actionDark = $action === null ? $this->actionDark : self::hex($action);
        $this->navigationDark = $navigation === null ? $this->navigationDark : self::hex($navigation);

        return $this;
    }

    /**
     * A hand-tuned 50–950 scale for the primary colour, instead of the generated one.
     *
     * @param  array<int, string>  $shades
     */
    public function primaryShades(array $shades): self
    {
        $this->primaryShades = $shades;

        return $this;
    }

    /**
     * A 50–950 neutral scale for this palette. The panel's own greys when not set.
     *
     * @param  array<int, string>  $shades
     */
    public function grayShades(array $shades): self
    {
        $this->grayShades = $shades;

        return $this;
    }

    /**
     * The logo shown on the navigation bar while this palette is in use. Without $dark the same
     * logo is used in light and dark mode.
     */
    public function brandLogo(string|Htmlable|Closure|null $logo, string|Htmlable|Closure|null $dark = null): self
    {
        $this->brandLogo = $logo;
        $this->darkModeBrandLogo = $dark;

        return $this;
    }

    /**
     * The compact logo shown while the sidebar is collapsed (a symbol, usually). Without $dark the
     * same one is used in light and dark mode.
     */
    public function collapsedBrandLogo(string|Htmlable|Closure|null $logo, string|Htmlable|Closure|null $dark = null): self
    {
        $this->collapsedBrandLogo = $logo;
        $this->darkModeCollapsedBrandLogo = $dark;

        return $this;
    }

    public function brandLogoHeight(?string $height): self
    {
        $this->brandLogoHeight = $height;

        return $this;
    }

    /** Marks a palette a user made for themselves. */
    public function custom(bool $condition = true): self
    {
        $this->isCustom = $condition;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label ?? Str::headline($this->id);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrimary(): string
    {
        return $this->primary;
    }

    public function getAction(bool $dark = false): string
    {
        $action = $this->action ?? $this->primary;

        return $dark ? ($this->actionDark ?? $action) : $action;
    }

    public function getNavigation(bool $dark = false): string
    {
        $navigation = $this->navigation ?? $this->getAction();

        if (! $dark) {
            return $navigation;
        }

        return $this->navigationDark ?? (self::isLight($navigation) ? self::DARK_NAVIGATION : $navigation);
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    /** @return list<string> */
    public function getNavigationGlow(): array
    {
        return $this->navigationGlow;
    }

    public function hasBrandLogo(): bool
    {
        return $this->brandLogo !== null;
    }

    public function getBrandLogo(bool $dark = false): string|Htmlable|null
    {
        return value($dark ? ($this->darkModeBrandLogo ?? $this->brandLogo) : $this->brandLogo);
    }

    public function hasCollapsedBrandLogo(): bool
    {
        return $this->collapsedBrandLogo !== null;
    }

    public function getCollapsedBrandLogo(bool $dark = false): string|Htmlable|null
    {
        return value($dark ? ($this->darkModeCollapsedBrandLogo ?? $this->collapsedBrandLogo) : $this->collapsedBrandLogo);
    }

    public function getBrandLogoHeight(): ?string
    {
        return $this->brandLogoHeight;
    }

    public function isCustom(): bool
    {
        return $this->isCustom;
    }

    /** Whether the navigation bar has a light background, and so needs dark text and a dark logo. */
    public function hasLightNavigation(bool $dark = false): bool
    {
        return self::isLight($this->getNavigation($dark));
    }

    /**
     * The colours Filament registers for this palette.
     *
     * @return array<string, array<int, string>>
     */
    public function filamentColors(): array
    {
        $colors = ['primary' => $this->primaryShades ?? Color::hex($this->primary)];

        if ($this->grayShades !== null) {
            $colors['gray'] = $this->grayShades;
        }

        return $colors;
    }

    /**
     * The CSS custom properties read by the package stylesheet.
     *
     * @return array<string, string>
     */
    public function cssVariables(bool $dark = false): array
    {
        $action = $this->getAction($dark);
        $navigation = $this->getNavigation($dark);
        $onLightNavigation = self::isLight($navigation);
        $ink = $onLightNavigation ? '17 17 17' : '255 255 255';

        $variables = [
            '--fap-primary' => $this->primary,
            '--fap-action' => $action,
            '--fap-on-action' => self::textOn($action),
            '--fap-navigation' => $navigation,
            '--fap-navigation-text' => self::textOn($navigation),
            '--fap-navigation-text-muted' => match (true) {
                $onLightNavigation => 'rgb(17 17 17 / 0.7)',
                $dark => 'rgb(250 250 250 / 0.68)',
                default => '#ffffff',
            },
            '--fap-navigation-hover' => "rgb({$ink} / 0.08)",
            '--fap-navigation-active' => "rgb({$ink} / ".($onLightNavigation ? '0.1' : '0.16').')',
            '--fap-navigation-ring' => "rgb({$ink} / 0.12)",
        ];

        if ($this->navigationGlow !== []) {
            $variables['--fap-navigation-image'] = $this->navigationImage();
        }

        if (! $dark && $this->background !== null) {
            $variables['--fap-background'] = $this->background;
        }

        return $variables;
    }

    /** The navigation glows as CSS gradients, built only from validated "#rrggbb" colours. */
    private function navigationImage(): string
    {
        [$first, $second] = $this->navigationGlow + [1 => null];

        $gradients = ["radial-gradient(150% 34% at 0% 100%, {$first}73, transparent 72%)"];

        if ($second !== null) {
            array_unshift($gradients, "radial-gradient(130% 38% at 100% 64%, {$second}99, transparent 70%)");
        }

        return implode(', ', $gradients);
    }

    /** White or near-black, whichever reads better on the given "#rrggbb" background. */
    public static function textOn(string $background): string
    {
        return self::contrast(self::LIGHT_TEXT, $background) >= self::contrast(self::DARK_TEXT, $background)
            ? self::LIGHT_TEXT
            : self::DARK_TEXT;
    }

    public static function isLight(string $color): bool
    {
        return self::textOn($color) === self::DARK_TEXT;
    }

    /** WCAG 2 contrast ratio between two "#rrggbb" colours (1 to 21). */
    public static function contrast(string $first, string $second): float
    {
        $lighter = max(self::luminance($first), self::luminance($second));
        $darker = min(self::luminance($first), self::luminance($second));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    public static function isHexColor(mixed $color): bool
    {
        return is_string($color) && preg_match(self::HEX_COLOR, $color) === 1;
    }

    /** Palette colours end up inside a style tag, so anything but "#rrggbb" is refused. */
    private static function hex(string $color): string
    {
        if (! self::isHexColor($color)) {
            throw new InvalidArgumentException("Palette colours must be \"#rrggbb\" hex codes, [{$color}] given.");
        }

        return strtolower($color);
    }

    private static function luminance(string $color): float
    {
        $channels = array_map(
            static function (string $pair): float {
                $value = hexdec($pair) / 255;

                return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
            },
            str_split(ltrim($color, '#'), 2),
        );

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
