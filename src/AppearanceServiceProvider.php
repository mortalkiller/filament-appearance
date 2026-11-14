<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance;

use Closure;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Livewire;
use MortalKiller\FilamentAppearance\Commands\InstallCommand;
use MortalKiller\FilamentAppearance\Livewire\PalettePicker;
use MortalKiller\FilamentAppearance\Support\BarSparkline;
use MortalKiller\FilamentAppearance\Support\ProgressBar;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class AppearanceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-appearance')
            ->hasConfigFile()
            ->hasMigrations(['create_user_palettes_table', 'add_palette_to_users_table'])
            ->hasTranslations()
            ->hasViews()
            ->hasCommand(InstallCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->scoped(AppearanceManager::class);
    }

    public function packageBooted(): void
    {
        Livewire::component('filament-appearance.palette-picker', PalettePicker::class);

        Stat::macro('progress', function (int|float|null $percent, string $color = 'primary'): Stat {
            /** @var Stat $this */ // @phpstan-ignore varTag.nativeType (a macro is bound to the Stat)
            return $this->description(ProgressBar::make($percent)->color($color));
        });

        Stat::macro('bars', function (array $values, string $color = 'primary', ?string $label = null, array $labels = [], ?Closure $formatValueUsing = null): Stat {
            /** @var Stat $this */ // @phpstan-ignore varTag.nativeType (a macro is bound to the Stat)
            return $this->description(BarSparkline::make($values)->color($color)->label($label)->labels($labels)->formatValueUsing($formatValueUsing));
        });

        // Filament's color() tints the description and the chart; this tints the figure itself
        // (warning, danger, success, info or primary) and leaves the description neutral.
        Stat::macro('valueColor', function (?string $color): Stat {
            /** @var Stat $this */ // @phpstan-ignore varTag.nativeType (a macro is bound to the Stat)
            return $this->extraAttributes(['data-fap-value-color' => $color], merge: true);
        });
    }
}
