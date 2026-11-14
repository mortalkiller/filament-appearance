<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MortalKiller\FilamentAppearance\Facades\Appearance;
use MortalKiller\FilamentAppearance\Models\CustomPalette;
use MortalKiller\FilamentAppearance\Palette;

/**
 * For the user model: the palette they chose and the ones they made.
 *
 * @mixin Model
 */
trait HasPalette
{
    /**
     * The palettes this user made for themselves.
     *
     * @return HasMany<CustomPalette, $this>
     */
    public function customPalettes(): HasMany
    {
        return $this->hasMany(CustomPalette::class, 'user_id');
    }

    /** The palette this user sees: the one they chose, or the default one. */
    public function currentPalette(): Palette
    {
        return Appearance::paletteFor($this);
    }

    /** The stored choice, as it is on the user: a palette id, "custom:{id}", or null. */
    public function getPaletteKey(): ?string
    {
        return Appearance::paletteKeyOf($this);
    }

    /**
     * Chooses a palette and saves the user. Null, or the default palette, clears the choice.
     * A palette this user may not use is refused.
     */
    public function usePalette(Palette|CustomPalette|string|null $palette): bool
    {
        return Appearance::choose($this, $palette);
    }
}
