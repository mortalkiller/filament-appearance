<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Fixtures;

use Filament\Pages\Page;

final class Home extends Page
{
    protected static ?string $slug = '/';

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Home';
}
