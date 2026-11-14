<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MortalKiller\FilamentAppearance\AppearanceManager;
use MortalKiller\FilamentAppearance\Database\Factories\CustomPaletteFactory;
use MortalKiller\FilamentAppearance\Palette;

/**
 * A colour palette a user made for themselves. It belongs to the user, so it follows them
 * everywhere they sign in.
 *
 * @property string $name
 * @property string $primary_color
 * @property string $navigation_color
 * @property string $background_color
 */
class CustomPalette extends Model
{
    /** @use HasFactory<CustomPaletteFactory> */
    use HasFactory;

    protected $fillable = ['name', 'primary_color', 'navigation_color', 'background_color'];

    public function getTable(): string
    {
        return config('filament-appearance.custom_palettes_table', 'user_palettes');
    }

    /** @return BelongsTo<Model, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(AppearanceManager::userModel(), 'user_id');
    }

    /** The value stored on the user when this palette is the chosen one. */
    public function key(): string
    {
        return AppearanceManager::CUSTOM_PREFIX.$this->getKey();
    }

    public function toPalette(): Palette
    {
        return Palette::make($this->key())
            ->label($this->name)
            ->description(__('filament-appearance::appearance.custom.created_by_you'))
            ->primary($this->primary_color)
            ->navigation($this->navigation_color)
            ->background($this->background_color)
            ->custom();
    }

    protected static function newFactory(): CustomPaletteFactory
    {
        return CustomPaletteFactory::new();
    }
}
