<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Fixtures;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use MortalKiller\FilamentAppearance\Concerns\HasPalette;

final class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasPalette;

    protected $table = 'users';

    protected $guarded = [];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
