<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Feature;

use Illuminate\Support\Facades\File;
use MortalKiller\FilamentAppearance\Commands\InstallCommand;
use MortalKiller\FilamentAppearance\Tests\TestCase;

final class InstallCommandTest extends TestCase
{
    private string $theme = 'resources/css/filament/admin/theme.css';

    protected function tearDown(): void
    {
        File::deleteDirectory(resource_path('css/filament'));
        File::delete(File::glob(database_path('migrations/*_palette*')));

        parent::tearDown();
    }

    public function test_it_adds_the_stylesheet_after_the_other_imports_of_the_theme(): void
    {
        File::ensureDirectoryExists(dirname(base_path($this->theme)));
        File::put(base_path($this->theme), "@import '../../../../vendor/filament/filament/resources/css/theme.css';\n\n@source '../../../../app/Filament/**/*';\n");

        $this->artisan('filament-appearance:install')->assertSuccessful();

        self::assertSame(
            "@import '../../../../vendor/filament/filament/resources/css/theme.css';\n"
            ."@import '../../../../vendor/mortalkiller/filament-appearance/resources/css/index.css';\n\n"
            ."@source '../../../../app/Filament/**/*';\n",
            File::get(base_path($this->theme)),
        );

        $this->artisan('filament-appearance:install')->assertSuccessful();

        self::assertSame(1, substr_count(File::get(base_path($this->theme)), 'filament-appearance'));
    }

    public function test_it_says_so_when_there_is_no_theme(): void
    {
        $this->artisan('filament-appearance:install')
            ->expectsOutputToContain('No panel theme found')
            ->assertSuccessful();
    }

    public function test_the_stylesheet_path_is_relative_to_the_theme(): void
    {
        self::assertSame(
            '../../vendor/mortalkiller/filament-appearance/resources/css/index.css',
            InstallCommand::relativeStylesheet('resources/css/app.css'),
        );
    }
}
