<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Fixtures;

use Filament\Pages\Page;
use UnitEnum;

final class Reports extends Page
{
    protected static ?string $slug = 'reports';

    protected static ?string $navigationLabel = 'Reports';

    protected static string|UnitEnum|null $navigationGroup = 'Analysis';

    protected static ?string $title = 'Reports';
}
