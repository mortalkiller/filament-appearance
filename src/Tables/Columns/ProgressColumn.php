<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tables\Columns;

use Filament\Tables\Columns\Column;
use MortalKiller\FilamentAppearance\Concerns\HasProgressBar;

/**
 * A table column whose state is a percentage, drawn as a bar with the number beside it.
 */
final class ProgressColumn extends Column
{
    use HasProgressBar;

    protected string $view = 'filament-appearance::tables.columns.progress-column';
}
