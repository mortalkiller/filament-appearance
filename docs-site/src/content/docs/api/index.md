---
title: API Reference
description: Public classes of Filament Appearance.
---

All classes live under `MortalKiller\FilamentAppearance`.

## AppearancePlugin

Created with `AppearancePlugin::make()` and passed to the panel's `plugins()`. `AppearancePlugin::get()` returns the one of the current panel.

| Method | Arguments | Default |
|---|---|---|
| `palettes()` | `array<Palette>\|Closure` | No palettes: one built-in amber palette |
| `defaultPalette()` | `string\|Closure\|null`, a registered palette id | The first palette. An id that is not registered throws `InvalidArgumentException` |
| `customPalettes()` | `bool\|Closure $condition = true`, `int $limit = 12` | Off |
| `brandLogo()` | `string\|Htmlable\|Closure\|null $logo`, `$dark = null` | The panel's own logo |
| `collapsedBrandLogo()` | `string\|Htmlable\|Closure\|null $logo`, `$dark = null` | No compact logo |
| `brandLogoHeight()` | `string\|Closure\|null` | The panel's own height |
| `topbarSearchAtStart()` | `bool\|Closure = true` | Off |
| `underlinedTabs()` | `bool\|Closure = true` | Off |
| `mobileTables()` | `bool\|Closure = true` | Off |
| `mobileEdgeToEdge()` | `bool\|Closure = true` | Off |
| `mobileTabBar()` | `bool\|Closure = true` | Off |
| `mobileTabBarItems()` | `array<NavigationItem>\|Closure` | No shortcuts: the bar has only "More" |
| `mobileTabBarAction()` | `string\|Htmlable\|Closure\|null` | No action |
| `appearancePage()` | `bool = true` | Off |

`AppearancePlugin::isOnSimplePage()` tells whether the current page is a sign-in page or another `SimplePage`.

The plugin sets the panel's `brandLogo()` only when a logo was given to the plugin or to one of the palettes passed as an array; palettes given as a closure are only known per request.

## Palette

`Palette::make(string $id)` and the fluent methods `label()`, `description()`, `primary()`, `action()`, `navigation()`, `background()`, `dark()`, `primaryShades()`, `grayShades()`, `brandLogo()`, `collapsedBrandLogo()`, `brandLogoHeight()` and `custom()`. Colours must be `#rrggbb`; anything else throws `InvalidArgumentException`.

Getters: `getId()`, `getLabel()`, `getDescription()`, `getPrimary()`, `getAction(bool $dark = false)`, `getNavigation(bool $dark = false)`, `getBackground()`, `hasBrandLogo()`, `getBrandLogo(bool $dark = false)`, `hasCollapsedBrandLogo()`, `getCollapsedBrandLogo(bool $dark = false)`, `getBrandLogoHeight()`, `isCustom()`, `hasLightNavigation(bool $dark = false)`, `filamentColors()` and `cssVariables(bool $dark = false)`.

Static helpers: `Palette::textOn($background)` returns white or near-black, `Palette::isLight($color)`, `Palette::contrast($first, $second)` returns the WCAG 2 ratio, and `Palette::isHexColor($value)`.

## Facades\Appearance

| Method | Returns |
|---|---|
| `plugin()` | The current panel's plugin, or null when the panel does not use it |
| `palettes()` | `array<string, Palette>` by id |
| `defaultPalette()` | `Palette` |
| `palette()` | The palette of whoever is signed in to the panel |
| `paletteFor(?Model $user)` | The user's palette, or the default one |
| `find(?string $key, ?Model $user = null)` | The palette under a stored key, as far as the user may use it |
| `choose(Model $user, Palette\|CustomPalette\|string\|null $palette)` | `bool`: false when the user may not use it |
| `paletteKeyOf(Model $user)` | The stored choice: an id, `custom:{id}` or null |
| `customPalettesOf(Model $user)` | `HasMany`; throws `LogicException` when the model does not use `HasPalette` |

## Concerns\HasPalette

For the user model: `customPalettes()` (a `HasMany` of `Models\CustomPalette`), `currentPalette()`, `getPaletteKey()` and `usePalette(Palette|CustomPalette|string|null $palette): bool`.

## Models\CustomPalette

A palette a user made: `name`, `primary_color`, `navigation_color`, `background_color`. `key()` is the value stored on the user (`custom:{id}`), `toPalette()` turns it into a `Palette`, and `user()` is the owner. It has a factory.

## Schemas\Components\PalettePicker

`PalettePicker::make()` places the picker in a schema. It renders the Livewire component `Livewire\PalettePicker`, which aborts with 403 for guests and for users whose model does not use `HasPalette`.

## Progress bars

- `Support\ProgressBar::make(int|float|null $percent)` with `color(string)` and `value(bool $condition = true, int $precision = 0)`. It is `Htmlable`.
- `Tables\Columns\ProgressColumn` and `Infolists\Components\ProgressEntry`, with `color(string|Closure)`, `precision(int|Closure)` (default 1) and `value(bool|Closure)`. A column with a `placeholder()` shows that text instead of the bar while its state is null.
- `Stat::progress(int|float|null $percent, string $color = 'primary')`, a macro that sets the stat's description.
- `Stat::bars(array $values, string $color = 'primary', ?string $label = null, array $labels = [], ?Closure $formatValueUsing = null)`, a macro that sets the stat's description to a `Support\BarSparkline`: one bar per value, the last in full colour; with labels, hovering a column shows a chart-style tooltip with the label and the formatted value.
- `Stat::valueColor(?string $color)`, a macro that puts the figure in a status colour (`warning`, `danger`, `success`, `info`, `primary`) and leaves the description neutral.

## Widgets\BreakdownChartWidget

An abstract widget. Implement `getSegments(): array` returning `BreakdownChartWidget\Segment` objects (`Segment::make(string $label, int|float $value)`, with `color(?string)` for a Filament colour name or any CSS colour). Properties: `$heading`, `$description`, `$color` (default `primary`) and `$maxSegments` (default and maximum 6). Overridable: `getHeading()`, `getDescription()`, `getColor()`, `getTotalLabel()`, `getOthersLabel()`, `getEmptyStateLabel()` and `formatValue(int|float $value): string`.

## Navigation\MobileMenu

`MobileMenu::tabs(array $items)` and `MobileMenu::sections(array $excludedUrls = [])` return the data behind the tab bar and its sheet, for building a menu of your own.

## View\AppearanceIconAlias

The icon aliases: `USER_MENU_APPEARANCE`, `PICKER_ACTIONS_CREATE`, `PICKER_ACTIONS_EDIT`, `PICKER_ACTIONS_DELETE`, `MOBILE_MENU_SEARCH`, `MOBILE_MENU_EXPAND`, `MOBILE_TAB_BAR_MORE` and `MOBILE_TAB_BAR_CLOSE`.

## Commands

`php artisan filament-appearance:install {--theme=*}` publishes the migrations and adds the stylesheet to the panel themes.
