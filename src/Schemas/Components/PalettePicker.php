<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Schemas\Components;

use Closure;
use Filament\Schemas\Components\Livewire;
use MortalKiller\FilamentAppearance\Livewire\PalettePicker as PalettePickerComponent;

/**
 * The palette picker as a schema component, for a profile page, a settings page or a modal.
 */
final class PalettePicker extends Livewire
{
    /**
     * @param  array<string, mixed>|Closure  $data
     */
    public static function make(string|Closure $component = PalettePickerComponent::class, array|Closure $data = []): static
    {
        return parent::make($component, $data);
    }
}
