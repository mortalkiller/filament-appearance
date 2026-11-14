<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Infolists\Components;

use Filament\Infolists\Components\Entry;
use MortalKiller\FilamentAppearance\Concerns\HasProgressBar;

/**
 * An infolist entry whose state is a percentage, drawn as a bar with the number beside it.
 */
final class ProgressEntry extends Entry
{
    use HasProgressBar;

    protected string $view = 'filament-appearance::infolists.components.progress-entry';
}
