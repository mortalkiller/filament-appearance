<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;
use MortalKiller\FilamentAppearance\Concerns\HasPalette;
use MortalKiller\FilamentAppearance\Models\CustomPalette;

/**
 * Answers "which palette?" for the current panel: the ones registered on its plugin, the default
 * one, and the one a user chose. Reached through the Appearance facade.
 */
final class AppearanceManager
{
    /** The stored choice is "custom:{id}" for a palette the user made. */
    public const CUSTOM_PREFIX = 'custom:';

    /** The plugin of the panel being served, or null when that panel does not use it. */
    public function plugin(): ?AppearancePlugin
    {
        $panel = Filament::getCurrentOrDefaultPanel();

        if ($panel === null || ! $panel->hasPlugin(AppearancePlugin::ID)) {
            return null;
        }

        /** @var AppearancePlugin $plugin */
        $plugin = $panel->getPlugin(AppearancePlugin::ID);

        return $plugin;
    }

    /**
     * The palettes registered on the panel, by id.
     *
     * @return array<string, Palette>
     */
    public function palettes(): array
    {
        return $this->plugin()?->getPalettes() ?? [];
    }

    public function defaultPalette(): Palette
    {
        return $this->plugin()?->getDefaultPalette() ?? Palette::make('default');
    }

    /**
     * The palette stored under $key, as far as $user may use it: any registered one, or a custom
     * one that belongs to them.
     */
    public function find(?string $key, ?Model $user = null): ?Palette
    {
        if ($key === null) {
            return null;
        }

        if (! str_starts_with($key, self::CUSTOM_PREFIX)) {
            return $this->palettes()[$key] ?? null;
        }

        if ($user === null || ! self::hasPalettes($user) || ! ($this->plugin()?->hasCustomPalettes() ?? false)) {
            return null;
        }

        return $this->customPalettesOf($user)
            ->find((int) substr($key, strlen(self::CUSTOM_PREFIX)))
            ?->toPalette();
    }

    /** The palette $user chose; the default one for guests, no choice, or a choice that is gone. */
    public function paletteFor(?Model $user): Palette
    {
        if ($user === null || ! self::hasPalettes($user)) {
            return $this->defaultPalette();
        }

        return $this->find($this->paletteKeyOf($user), $user) ?? $this->defaultPalette();
    }

    /** The stored choice, as it is on the user: a palette id, "custom:{id}", or null. */
    public function paletteKeyOf(Model $user): ?string
    {
        $key = $user->getAttribute(self::paletteColumn());

        return is_string($key) ? $key : null;
    }

    /**
     * Chooses a palette for $user and saves them. Null, or the default palette, clears the
     * choice. A palette they may not use is refused.
     */
    public function choose(Model $user, Palette|CustomPalette|string|null $palette): bool
    {
        $key = match (true) {
            $palette instanceof Palette => $palette->getId(),
            $palette instanceof CustomPalette => $palette->key(),
            default => $palette,
        };

        if ($key !== null && $this->find($key, $user) === null) {
            return false;
        }

        $user->setAttribute(self::paletteColumn(), $key === $this->defaultPalette()->getId() ? null : $key);

        return $user->save();
    }

    /**
     * The palettes $user made. They must use the HasPalette trait.
     *
     * @return HasMany<CustomPalette, Model>
     */
    public function customPalettesOf(Model $user): HasMany
    {
        if (! self::hasPalettes($user)) {
            throw new LogicException('['.$user::class.'] must use the HasPalette trait to have palettes of its own.');
        }

        /** @phpstan-ignore method.notFound (the trait is checked above) */
        return $user->customPalettes();
    }

    /** The palette of whoever is signed in to the panel. */
    public function palette(): Palette
    {
        return $this->paletteFor($this->user());
    }

    public function user(): ?Model
    {
        $user = Filament::auth()->user();

        return $user instanceof Model ? $user : null;
    }

    /** @return class-string<Model> */
    public static function userModel(): string
    {
        /** @var class-string<Model> $model */
        $model = config('filament-appearance.user_model') ?? config('auth.providers.users.model');

        return $model;
    }

    public static function paletteColumn(): string
    {
        return config('filament-appearance.palette_column', 'palette');
    }

    public static function hasPalettes(Model $user): bool
    {
        return in_array(HasPalette::class, class_uses_recursive($user), true);
    }
}
