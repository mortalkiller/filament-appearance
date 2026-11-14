<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use MortalKiller\FilamentAppearance\Schemas\Components\PalettePicker;

/**
 * A page with the palette picker, for panels that have no profile page to put it on.
 * Registered by AppearancePlugin::appearancePage().
 */
final class Appearance extends Page
{
    protected static ?string $slug = 'appearance';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('filament-appearance::appearance.page.title');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            PalettePicker::make(),
        ]);
    }
}
