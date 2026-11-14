---
title: Configuration
description: Palettes, logos, the palette picker, the shell and the config file.
---

Everything about a panel's appearance is set on the plugin, where it can read the panel and accept closures. The config file only holds what is about the database.

## Palettes

| Method | What it sets | When left out |
|---|---|---|
| `primary($color)` | Filament's primary scale: links, focus rings, charts | Amber (`#f59e0b`) |
| `action($color, dark: …)` | Main button background | The primary colour |
| `navigation($color, dark: …)` | Navigation bar background | The action colour |
| `background($color)` | Page background behind cards, in light mode | Filament's own |
| `navigationGlow($color, $second)` | Soft glows of colour over the sidebar, in both modes: the first from the bottom corner, the second from the far side halfway down | A plain sidebar |
| `dark(action: …, navigation: …)` | The colours that change in dark mode | Same as light mode; a light navigation bar turns near-black |
| `primaryShades([...])` | A hand-tuned 50–950 primary scale | Generated from the primary colour |
| `grayShades([...])` | A neutral scale for this palette | The panel's greys |
| `label()`, `description()` | What the picker shows | A headline of the id; nothing |

Colours are `#rrggbb` hex codes; anything else throws an `InvalidArgumentException`. The text on the navigation bar and on the main button turns white or near-black by itself, whichever has the better contrast.

`palettes()` and `defaultPalette()` accept closures. Without `defaultPalette()`, the first palette is the default one.

Let users make their own palettes from three colours (main, navigation, background):

```php
AppearancePlugin::make()
    ->palettes([...])
    ->customPalettes(limit: 12)
```

## Logos

A palette can bring its own logo. Give one logo to use it in light and dark mode, or one for each:

```php
Palette::make('forest')
    ->primary('#18b980')
    ->navigation('#0a5b42')
    ->brandLogo(asset('images/logo-white.svg')),

Palette::make('paper')
    ->primary('#2563eb')
    ->navigation('#ffffff', dark: '#111827')
    ->brandLogo(asset('images/logo-black.svg'), dark: asset('images/logo-white.svg')),
```

Set the panel's own pair on the plugin instead of on the panel:

```php
AppearancePlugin::make()
    ->brandLogo(asset('images/logo-black.svg'), dark: asset('images/logo-white.svg'))
```

The first is for light surfaces and the second for dark ones. They are used on the sign-in pages, and on the navigation bar of every palette without a logo of its own (including the ones users make), where the bar's colour decides which of the two is shown. `brandLogoHeight()` exists on the plugin and on a palette. All of them accept a string, an `Htmlable` or a closure.

### A collapsed sidebar

On a panel with `sidebarCollapsibleOnDesktop()`, give a compact logo (a symbol, usually) for the collapsed state. It follows the same rules as the full logo: a pair on the plugin, picked by the colour of the navigation bar, and optionally one per palette.

```php
AppearancePlugin::make()
    ->collapsedBrandLogo(asset('images/symbol-black.svg'), dark: asset('images/symbol-white.svg'))

Palette::make('forest')
    ->primary('#18b980')
    ->collapsedBrandLogo(asset('images/symbol-white.svg'))
```

Without one, the corner above the collapsed sidebar is left empty.

Filament hides a collapsible sidebar until its JavaScript starts, which leaves every full page load without a sidebar for a moment. With this package the sidebar is drawn from the first paint, open or collapsed as the user left it.

## The palette picker

Place the picker in any schema: a profile page, a settings page, a modal.

```php
use MortalKiller\FilamentAppearance\Schemas\Components\PalettePicker;

public function content(Schema $schema): Schema
{
    return $schema->components([
        PalettePicker::make(),
    ]);
}
```

Or let the plugin add a page with it, linked from the user menu:

```php
AppearancePlugin::make()->appearancePage()
```

With neither, palettes still apply but users cannot change theirs, which is a way to fix one palette per panel.

## With filament-complete-user-profile

[`mortalkiller/filament-complete-user-profile`](https://github.com/mortalkiller/filament-complete-user-profile) is not required, but the two fit together: add the picker as a section of the profile and leave `appearancePage()` off.

```php
use Mortalkiller\FilamentCompleteUserProfile\AccountSection;
use Mortalkiller\FilamentCompleteUserProfile\CompleteUserProfilePlugin;
use MortalKiller\FilamentAppearance\AppearancePlugin;
use MortalKiller\FilamentAppearance\Schemas\Components\PalettePicker;

->plugins([
    AppearancePlugin::make()
        ->palettes([...])
        ->customPalettes(),

    CompleteUserProfilePlugin::make()
        ->section(
            AccountSection::make('appearance')
                ->label('Appearance')
                ->description('Choose the colours and the mode of the interface.')
                ->sort(35)
                ->schema([PalettePicker::make()]),
        ),
])
```

This package's stylesheet is plain CSS, so it needs no `@source` line of its own in your theme.

## The shell

Each part is off by default and accepts a boolean or a closure:

```php
AppearancePlugin::make()
    ->topbarSearchAtStart() // on desktop, the global search sits at the start of the topbar
    ->tableSearchAtStart()  // in tables, the search first and the toolbar actions last
    ->underlinedTabs()      // tabs as a row of labels with a line under the active one
    ->mobileTables()        // below 768px, tables become lists of cards
    ->mobileEdgeToEdge()    // below 768px, sections, tables and stats span the screen
    ->mobileTabBar()        // below 1024px, a bar of shortcuts at the bottom
```

On a phone list, the first plain-text column becomes the title of each card and the other cells are labelled with their column heading. Sorting by column and "select all" stay desktop-only. Give any block of your own the class `fap-edge-to-edge` to have it span the screen like a section.

Breadcrumbs take the palette too: the links back are in its colour and the current page stays muted, a little smaller than the text around them. Filament draws every crumb in the same grey, so the links do not read as links.

### Sections with the same height

Filament stretches the cells of a grid row but not what is inside them, so a short section ends above a taller neighbour. Add the class `fap-equal-height` to a grid whose cells hold one widget or section each, and they all take the height of the tallest:

```php
Grid::make(['default' => 1, 'xl' => 3])
    ->extraAttributes(['class' => 'fap-equal-height'])
    ->schema([...])
```

### A radio as cards

Add the class `fap-radio-cards` to a Filament `Radio` and each option becomes a card with its label and description, the chosen one in the palette's soft colour with an outline. Give it as many columns as options:

```php
Radio::make('kind')
    ->options(ExpenseKind::class)
    ->columns(2)
    ->extraAttributes(['class' => 'fap-radio-cards'])
```

### Record actions under the content

In a table with a content layout (`Split` / `Stack` columns), Filament puts each record's actions under its content on a phone and at the end of the row on wider screens. Add the class `fap-actions-below` to keep them under the content at every width, for lists where the actions are the point of each row:

```php
$table
    ->columns([Split::make([...])])
    ->extraAttributes(['class' => 'fap-actions-below'])
```

### A details card as a key/value list

Filament gives an inline label a third of the width and the weight of a heading, so in a narrow card short labels break onto two lines. Add the class `fap-key-value` to a section with inline-labelled entries and each one sits on a single line: the label as wide as its text and muted, the value at the end, the lines close together. Entries without an inline label (a description, notes) keep Filament's layout:

```php
Section::make('Details')
    ->inlineLabel()
    ->extraAttributes(['class' => 'fap-key-value'])
    ->schema([
        TextEntry::make('category.name')->label('Category'),
        TextEntry::make('net_amount')->label('Amount excluding VAT')->money('EUR'),
    ])
```

The line the others add up to takes `fap-key-value-total`: a rule above it and its label in the weight and colour of the value:

```php
TextEntry::make('total')
    ->label('Total')
    ->money('EUR')
    ->weight('bold')
    ->extraEntryWrapperAttributes(['class' => 'fap-key-value-total'])
```

### A table of entries without the box

Filament draws a table of entries as a card of its own: a ring, a grey heading and rules between the columns. Inside a section that already has its frame, add the class `fap-plain-table` and it becomes lines on the page, with only a rule under the heading and between the rows, the heading small and muted, and the first and last cells flush with the edges. On a narrow container the rows still stack as Filament stacks them, without the box:

```php
RepeatableEntry::make('lines')
    ->table([
        TableColumn::make('Description'),
        TableColumn::make('Total')->alignment(Alignment::End),
    ])
    ->extraAttributes(['class' => 'fap-plain-table'])
```

### Labels as small capitals

Add the class `fap-caps-labels` to a grid or a section and the labels of the entries inside it become small capitals: 12px, upper case, spaced and muted, the way a printed document names its fields. Entries outside it keep Filament's labels:

```php
Grid::make(4)
    ->extraAttributes(['class' => 'fap-caps-labels'])
    ->schema([
        TextEntry::make('date')->label('Date')->date(),
        TextEntry::make('due_date')->label('Due')->date(),
    ])
```

### Rules between the parts of a card

`fap-rule-top` and `fap-rule-bottom` put a hairline above or below any component, with room between the two, to separate the parts of a card without splitting it into sections. `fap-divided` does the same between the rows of a section, for a card that is a short list:

```php
Grid::make(4)->extraAttributes(['class' => 'fap-rule-top fap-rule-bottom'])

Section::make('History')->extraAttributes(['class' => 'fap-divided'])
```

`fap-part` is for the blocks of a form opened in a modal: give each block an uncontained section with the class, and every block after the first sits under a hairline instead of in a card of its own:

```php
Section::make('Tax details')->contained(false)->extraAttributes(['class' => 'fap-part'])
```

## The phone tab bar

```php
use Filament\Navigation\NavigationItem;

AppearancePlugin::make()
    ->mobileTabBar(fn (): bool => filament()->getTenant() !== null)
    ->mobileTabBarItems(fn (): array => [
        NavigationItem::make('Home')->icon(Heroicon::OutlinedHome)->url(Dashboard::getUrl()),
        NavigationItem::make('Orders')
            ->icon(Heroicon::OutlinedShoppingBag)
            ->url(OrderResource::getUrl())
            ->isActiveWhen(fn (): bool => request()->routeIs(OrderResource::getRouteBaseName().'.*')),
    ])
    ->mobileTabBarAction(fn (): string => view('partials.new-order-button')->render())
```

The last button is always "More": it opens a sheet with the rest of the panel's navigation, searchable. Open navigation groups show their pages as tiles; groups declared with `->collapsed()` are one row that opens a list. An item is active when its URL is the current one, or when its own `isActiveWhen()` says so. The optional action sits in the middle of the bar. The bar is never shown on the sign-in pages or to guests.

## Progress bars

```php
use MortalKiller\FilamentAppearance\Infolists\Components\ProgressEntry;
use MortalKiller\FilamentAppearance\Support\ProgressBar;
use MortalKiller\FilamentAppearance\Tables\Columns\ProgressColumn;

ProgressColumn::make('margin_percent')
    ->color(fn (?float $state): string => $state < 30 ? 'warning' : 'primary')
    ->precision(1);

ProgressEntry::make('share');

Stat::make('Approved', '3 of 5')->progress(60);

Stat::make('Used', '80%')->description(ProgressBar::make(80)->color('danger'));
```

The state is a percentage. `color()` takes a Filament colour name (`primary`, `success`, `warning`, `danger`, `info`, `gray`) or a closure; a null state draws an empty grey bar. In a view of your own: `<span class="fap-progress"><i style="width: 60%"></i></span>`.

## Stats in one card

Filament draws every stat of a `StatsOverviewWidget` as its own card. Add the `GroupsStats` trait to a widget to put its stats side by side in one card instead:

```php
use MortalKiller\FilamentAppearance\Widgets\Concerns\GroupsStats;

class OrderSummary extends StatsOverviewWidget
{
    use GroupsStats;
}
```

With `underlinedTabs()`, the count on a tab is a round pill in a neutral tone. A count given a status colour (`warning`, `danger`, `success`, `info`) keeps that colour.

## Breakdown charts

How a total splits into parts: a ring with the total in the middle and a legend with the amount and the share of each part. It is drawn in SVG and CSS, in shades of one Filament colour, so it follows the palette and dark mode without any JavaScript.

```php
use MortalKiller\FilamentAppearance\Widgets\BreakdownChartWidget;
use MortalKiller\FilamentAppearance\Widgets\BreakdownChartWidget\Segment;

class SalesByChannel extends BreakdownChartWidget
{
    protected ?string $heading = 'Sales by channel';

    protected ?string $description = 'This year.';

    protected string $color = 'primary';

    protected function getSegments(): array
    {
        return [
            Segment::make('Online', 2400),
            Segment::make('Shop', 850),
            Segment::make('Refunds', 120)->color('danger'),
        ];
    }

    protected function formatValue(int|float $value): string
    {
        return Number::currency($value, 'EUR');
    }
}
```

Parts are shown largest first and those at zero are left out. Past `$maxSegments` (six at most) the smallest are folded into one, labelled by `getOthersLabel()`. `getHeading()`, `getDescription()`, `getColor()` and `getTotalLabel()` can be overridden for values that depend on the request.

## Reading the palette

```php
use MortalKiller\FilamentAppearance\Facades\Appearance;

Appearance::palette();                 // the signed-in user's palette
Appearance::palettes();                // the ones registered on the panel, by id
Appearance::defaultPalette();

$user->currentPalette();               // a Palette
$user->usePalette('ink');              // false when the user may not use it
$user->customPalettes;                 // the ones they made
```

In your own CSS, the palette is available as custom properties: `--fap-primary`, `--fap-action`, `--fap-on-action`, `--fap-navigation`, `--fap-navigation-text` and `--fap-background`.

## Icons and translations

The package's own icons are Heroicons registered under aliases, so you can swap them:

```php
use Filament\Support\Facades\FilamentIcon;
use MortalKiller\FilamentAppearance\View\AppearanceIconAlias;

FilamentIcon::register([
    AppearanceIconAlias::MOBILE_TAB_BAR_MORE => 'heroicon-o-ellipsis-horizontal',
]);
```

Publish the translations with `php artisan vendor:publish --tag=filament-appearance-translations`.

## Config file

```bash
php artisan vendor:publish --tag=filament-appearance-config
```

| Key | Default | Purpose |
|---|---|---|
| `user_model` | The default auth provider's model | The model that uses `HasPalette` |
| `palette_column` | `palette` | The column that holds the user's choice |
| `custom_palettes_table` | `user_palettes` | The table for the palettes users make |

Set these before running the migrations.
