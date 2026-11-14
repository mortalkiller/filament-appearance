<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Publishes the migrations and adds the package stylesheet to the panel themes it finds.
 */
#[AsCommand(name: 'filament-appearance:install')]
final class InstallCommand extends Command
{
    private const STYLESHEET = 'vendor/mortalkiller/filament-appearance/resources/css/index.css';

    protected $signature = 'filament-appearance:install {--theme=* : Path of a panel theme stylesheet, relative to the application}';

    protected $description = 'Publish the Filament Appearance migrations and add its stylesheet to the panel themes';

    public function handle(Filesystem $files): int
    {
        $this->callSilently('vendor:publish', ['--tag' => 'filament-appearance-migrations']);
        $this->components->info('Migrations published. Run "php artisan migrate" to create the palette tables.');

        /** @var list<string> $themes */
        $themes = $this->option('theme') ?: array_map(
            fn (string $path): string => ltrim(str_replace(base_path(), '', $path), DIRECTORY_SEPARATOR),
            $files->glob(resource_path('css/filament/*/theme.css')) ?: [],
        );

        if ($themes === []) {
            $this->components->warn('No panel theme found. Create one with "php artisan make:filament-theme", then run this command again.');

            return self::SUCCESS;
        }

        foreach ($themes as $theme) {
            $this->addStylesheetTo($files, $theme);
        }

        $this->components->info('Rebuild your assets ("npm run build") to see the changes.');

        return self::SUCCESS;
    }

    private function addStylesheetTo(Filesystem $files, string $theme): void
    {
        $path = base_path($theme);

        if (! $files->exists($path)) {
            $this->components->warn("[{$theme}] does not exist.");

            return;
        }

        $contents = $files->get($path);

        if (str_contains($contents, 'filament-appearance/resources/css')) {
            $this->components->twoColumnDetail($theme, 'already imports the stylesheet');

            return;
        }

        $import = "@import '".self::relativeStylesheet($theme)."';";
        $lines = explode("\n", $contents);

        // An @import has to come before any other rule: put it after the last leading @import.
        $position = 0;

        foreach ($lines as $index => $line) {
            if (str_starts_with(ltrim($line), '@import')) {
                $position = $index + 1;
            }
        }

        array_splice($lines, $position, 0, [$import]);
        $files->put($path, implode("\n", $lines));

        $this->components->twoColumnDetail($theme, 'stylesheet added');
    }

    /** The stylesheet's path as seen from the theme file, e.g. "../../../../vendor/…". */
    public static function relativeStylesheet(string $theme): string
    {
        $depth = substr_count(trim(str_replace('\\', '/', $theme), '/'), '/');

        return str_repeat('../', $depth).self::STYLESHEET;
    }
}
