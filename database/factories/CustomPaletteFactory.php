<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MortalKiller\FilamentAppearance\AppearanceManager;
use MortalKiller\FilamentAppearance\Models\CustomPalette;

/**
 * @extends Factory<CustomPalette>
 */
final class CustomPaletteFactory extends Factory
{
    protected $model = CustomPalette::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => AppearanceManager::userModel()::factory(),
            'name' => fake()->unique()->colorName(),
            'primary_color' => '#9f1239',
            'navigation_color' => '#3b0d1a',
            'background_color' => '#faf5f5',
        ];
    }
}
