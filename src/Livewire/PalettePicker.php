<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Livewire;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Component;
use MortalKiller\FilamentAppearance\AppearanceManager;
use MortalKiller\FilamentAppearance\Facades\Appearance;
use MortalKiller\FilamentAppearance\Models\CustomPalette;
use MortalKiller\FilamentAppearance\Palette;
use MortalKiller\FilamentAppearance\View\AppearanceIconAlias;

/**
 * Light/dark mode, the panel's palettes, and the user's own palettes. The choice is saved on the
 * user. Colours are set in the page head, so the page reloads after each change.
 */
final class PalettePicker extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function selectPalette(string $key): void
    {
        if (Appearance::choose($this->user(), $key)) {
            $this->reloadPage();
        }
    }

    public function createPaletteAction(): Action
    {
        return Action::make('createPalette')
            ->label(__('filament-appearance::appearance.actions.create.label'))
            ->icon(FilamentIcon::resolve(AppearanceIconAlias::PICKER_ACTIONS_CREATE) ?? Heroicon::OutlinedPlus)
            ->color('gray')
            ->visible(fn (): bool => $this->canCreatePalette())
            ->modalHeading(__('filament-appearance::appearance.actions.create.modal_heading'))
            ->modalDescription(__('filament-appearance::appearance.actions.create.modal_description'))
            ->modalSubmitActionLabel(__('filament-appearance::appearance.actions.create.submit'))
            ->modalWidth('lg')
            ->fillForm(fn (): array => $this->colorsOf(Appearance::palette()) + ['base' => Appearance::palette()->getId()])
            ->schema([
                $this->nameField(),
                Select::make('base')
                    ->label(__('filament-appearance::appearance.fields.base'))
                    ->options(fn (): array => $this->paletteOptions())
                    ->selectablePlaceholder(false)
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set): void {
                        $base = Appearance::find($state, $this->user());

                        foreach ($base === null ? [] : $this->colorsOf($base) as $field => $color) {
                            $set($field, $color);
                        }
                    })
                    ->dehydrated(false),
                ...$this->colorFields(),
            ])
            ->action(function (array $data): void {
                abort_unless($this->canCreatePalette(), 403);

                $palette = $this->customPalettes()->create($data);

                $this->selectPalette($palette->key());
            });
    }

    public function editPaletteAction(): Action
    {
        return Action::make('editPalette')
            ->label(__('filament-appearance::appearance.actions.edit.label'))
            ->icon(FilamentIcon::resolve(AppearanceIconAlias::PICKER_ACTIONS_EDIT) ?? Heroicon::OutlinedPencilSquare)
            ->iconButton()
            ->color('gray')
            ->modalHeading(fn (array $arguments): string => __('filament-appearance::appearance.actions.edit.modal_heading', ['name' => $this->customPalette($arguments)->name]))
            ->modalSubmitActionLabel(__('filament-appearance::appearance.actions.edit.submit'))
            ->modalWidth('lg')
            ->fillForm(fn (array $arguments): array => $this->customPalette($arguments)->only(['name', 'primary_color', 'navigation_color', 'background_color']))
            ->schema([
                $this->nameField(),
                ...$this->colorFields(),
            ])
            ->action(function (array $data, array $arguments): void {
                $this->customPalette($arguments)->update($data);

                $this->reloadPage();
            });
    }

    public function deletePaletteAction(): Action
    {
        return Action::make('deletePalette')
            ->label(__('filament-appearance::appearance.actions.delete.label'))
            ->icon(FilamentIcon::resolve(AppearanceIconAlias::PICKER_ACTIONS_DELETE) ?? Heroicon::OutlinedTrash)
            ->iconButton()
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments): string => __('filament-appearance::appearance.actions.delete.modal_heading', ['name' => $this->customPalette($arguments)->name]))
            ->modalDescription(__('filament-appearance::appearance.actions.delete.modal_description'))
            ->modalSubmitActionLabel(__('filament-appearance::appearance.actions.delete.submit'))
            ->action(function (array $arguments): void {
                $user = $this->user();
                $palette = $this->customPalette($arguments);

                if (Appearance::paletteKeyOf($user) === $palette->key()) {
                    Appearance::choose($user, null);
                }

                $palette->delete();

                Notification::make()
                    ->title(__('filament-appearance::appearance.notifications.deleted'))
                    ->success()
                    ->send();

                $this->reloadPage();
            });
    }

    public function render(): View
    {
        $allowsCustomPalettes = Appearance::plugin()?->hasCustomPalettes() ?? false;

        return view('filament-appearance::palette-picker', [
            'current' => Appearance::palette(),
            'palettes' => Appearance::palettes(),
            'allowsCustomPalettes' => $allowsCustomPalettes,
            'customPalettes' => $allowsCustomPalettes
                ? $this->customPalettes()->orderBy('name')->get()
                : collect(),
        ]);
    }

    private function canCreatePalette(): bool
    {
        $plugin = Appearance::plugin();

        return $plugin !== null
            && $plugin->hasCustomPalettes()
            && $this->customPalettes()->count() < $plugin->getCustomPalettesLimit();
    }

    private function nameField(): TextInput
    {
        return TextInput::make('name')
            ->label(__('filament-appearance::appearance.fields.name'))
            ->required()
            ->maxLength(40);
    }

    /**
     * The three colours a custom palette is made of.
     *
     * @return list<ColorPicker>
     */
    private function colorFields(): array
    {
        $field = fn (string $name, string $key): ColorPicker => ColorPicker::make($name)
            ->label(__("filament-appearance::appearance.fields.{$key}.label"))
            ->helperText(__("filament-appearance::appearance.fields.{$key}.helper"))
            ->required()
            ->regex('/^#[0-9a-fA-F]{6}$/');

        return [
            $field('primary_color', 'primary'),
            $field('navigation_color', 'navigation'),
            $field('background_color', 'background'),
        ];
    }

    /**
     * @return array{primary_color: string, navigation_color: string, background_color: string}
     */
    private function colorsOf(Palette $palette): array
    {
        return [
            'primary_color' => $palette->getAction(),
            'navigation_color' => $palette->getNavigation(),
            'background_color' => $palette->getBackground() ?? '#f9fafb',
        ];
    }

    /**
     * The panel's palettes and the user's own, to start a new one from.
     *
     * @return array<string, string>
     */
    private function paletteOptions(): array
    {
        $options = array_map(fn (Palette $palette): string => $palette->getLabel(), Appearance::palettes());

        foreach ($this->customPalettes()->orderBy('name')->get() as $palette) {
            $options[$palette->key()] = $palette->name;
        }

        return $options;
    }

    /**
     * The palette an edit or delete action was opened for. Only ever one of the user's own.
     *
     * @param  array<string, mixed>  $arguments
     */
    private function customPalette(array $arguments): CustomPalette
    {
        return $this->customPalettes()->findOrFail($arguments['palette'] ?? null);
    }

    /**
     * @return HasMany<CustomPalette, Model>
     */
    private function customPalettes(): HasMany
    {
        return Appearance::customPalettesOf($this->user());
    }

    private function user(): Model
    {
        $user = Appearance::user();

        abort_unless($user !== null && AppearanceManager::hasPalettes($user), 403);

        return $user;
    }

    private function reloadPage(): void
    {
        $this->js('window.location.reload()');
    }
}
