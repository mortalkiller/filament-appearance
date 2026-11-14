---
title: Installation
description: Install Filament Appearance and add its stylesheet to a panel theme.
---

The panel needs a [custom theme](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme): the package stylesheet is imported from it.

```bash
composer require mortalkiller/filament-appearance
```

```bash
php artisan filament-appearance:install
```

```bash
php artisan migrate
```

The install command publishes two migrations (a `palette` column on the users table and a `user_palettes` table) and adds one line to each panel theme it finds in `resources/css/filament/*/theme.css`, after the theme's other imports:

```css
@import '../../../../vendor/mortalkiller/filament-appearance/resources/css/index.css';
```

Pass `--theme=path/to/theme.css` when the theme lives somewhere else. The command can be run again: a theme that already imports the stylesheet is left alone. Then rebuild your assets.

```bash
npm run build
```

Add the trait to the user model:

```php
use MortalKiller\FilamentAppearance\Concerns\HasPalette;

class User extends Authenticatable
{
    use HasPalette;
}
```

Register the plugin on the panel:

```php
use MortalKiller\FilamentAppearance\AppearancePlugin;
use MortalKiller\FilamentAppearance\Palette;

->plugins([
    AppearancePlugin::make()
        ->palettes([
            Palette::make('forest')->primary('#18b980')->action('#0a5b42'),
            Palette::make('ink')->primary('#2563eb')->navigation('#111827'),
        ])
        ->appearancePage(),
])
```
