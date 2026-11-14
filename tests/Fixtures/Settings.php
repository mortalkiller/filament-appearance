<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Fixtures;

use Filament\Pages\Page;
use UnitEnum;

final class Settings extends Page
{
    protected static ?string $slug = 'settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $title = 'Settings';
}
