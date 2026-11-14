<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Facade;
use MortalKiller\FilamentAppearance\AppearanceManager;
use MortalKiller\FilamentAppearance\AppearancePlugin;
use MortalKiller\FilamentAppearance\Models\CustomPalette;
use MortalKiller\FilamentAppearance\Palette;

/**
 * @method static AppearancePlugin|null plugin()
 * @method static array<string, Palette> palettes()
 * @method static Palette defaultPalette()
 * @method static Palette|null find(?string $key, ?Model $user = null)
 * @method static Palette paletteFor(?Model $user)
 * @method static Palette palette()
 * @method static string|null paletteKeyOf(Model $user)
 * @method static bool choose(Model $user, Palette|CustomPalette|string|null $palette)
 * @method static HasMany<CustomPalette, Model> customPalettesOf(Model $user)
 * @method static Model|null user()
 *
 * @see AppearanceManager
 */
final class Appearance extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AppearanceManager::class;
    }
}
