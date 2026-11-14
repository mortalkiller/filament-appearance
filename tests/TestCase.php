<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests;

use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use MortalKiller\FilamentAppearance\AppearanceServiceProvider;
use MortalKiller\FilamentAppearance\Tests\Fixtures\PlainPanelProvider;
use MortalKiller\FilamentAppearance\Tests\Fixtures\TestPanelProvider;
use MortalKiller\FilamentAppearance\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected $enablesPackageDiscoveries = true;

    /** The panel each test runs in: `test` uses the plugin, `plain` does not. */
    protected string $panel = 'test';

    protected function getPackageProviders($app): array
    {
        return [
            AppearanceServiceProvider::class,
            TestPanelProvider::class,
            PlainPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('x', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();

        Filament::setCurrentPanel(Filament::getPanel($this->panel));
    }

    /** The in-memory database is new for every test: a users table, then the package's own. */
    private function migrate(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        foreach (['add_palette_to_users_table', 'create_user_palettes_table'] as $migration) {
            (require __DIR__."/../database/migrations/{$migration}.php.stub")->up();
        }
    }

    protected function signIn(?User $user = null): User
    {
        $user ??= User::factory()->create();

        $this->actingAs($user);

        return $user;
    }
}
