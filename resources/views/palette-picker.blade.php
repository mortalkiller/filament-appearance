{{--
    MortalKiller\FilamentAppearance\Livewire\PalettePicker. Each palette is a button with a small
    drawing of the panel in that palette's own colours. Styles: resources/css/picker.css.
--}}
@php
    use MortalKiller\FilamentAppearance\Palette;

    $thumbnail = fn (Palette $palette): string => implode('; ', [
        '--fap-thumb-navigation: '.$palette->getNavigation(),
        '--fap-thumb-navigation-text: '.Palette::textOn($palette->getNavigation()),
        '--fap-thumb-background: '.($palette->getBackground() ?? '#f9fafb'),
        '--fap-thumb-action: '.$palette->getAction(),
        '--fap-thumb-primary: '.$palette->getPrimary(),
    ]);
@endphp

<div class="fap-picker">
    <x-filament::section>
        <x-slot name="heading">{{ __('filament-appearance::appearance.mode.heading') }}</x-slot>
        <x-slot name="description">{{ __('filament-appearance::appearance.mode.description') }}</x-slot>

        <div class="fap-picker-mode">
            <x-filament-panels::theme-switcher />
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">{{ __('filament-appearance::appearance.palettes.heading') }}</x-slot>
        <x-slot name="description">{{ __('filament-appearance::appearance.palettes.description') }}</x-slot>

        <div class="fap-palette-grid" role="radiogroup" aria-label="{{ __('filament-appearance::appearance.palettes.label') }}">
            @foreach ($palettes as $palette)
                <button
                    type="button"
                    role="radio"
                    aria-checked="{{ $palette->getId() === $current->getId() ? 'true' : 'false' }}"
                    wire:click="selectPalette(@js($palette->getId()))"
                    wire:key="palette-{{ $palette->getId() }}"
                    class="fap-palette-card"
                >
                    <span class="fap-palette-thumb" style="{{ $thumbnail($palette) }}" aria-hidden="true">
                        <span class="fap-palette-thumb-bar"><i></i><i></i><i></i></span>
                        <span class="fap-palette-thumb-page"><span><i></i><i></i><b></b></span></span>
                    </span>
                    <span class="fap-palette-name">{{ $palette->getLabel() }}</span>
                    <span class="fap-palette-note">{{ $palette->getId() === $current->getId() ? __('filament-appearance::appearance.palettes.in_use') : $palette->getDescription() }}</span>
                </button>
            @endforeach
        </div>
    </x-filament::section>

    @if ($allowsCustomPalettes)
        <x-filament::section>
            <x-slot name="heading">{{ __('filament-appearance::appearance.custom.heading') }}</x-slot>
            <x-slot name="description">{{ __('filament-appearance::appearance.custom.description') }}</x-slot>

            @if ($customPalettes->isEmpty())
                <p class="fap-palette-empty">{{ __('filament-appearance::appearance.custom.empty') }}</p>
            @else
                <div class="fap-palette-grid" role="radiogroup" aria-label="{{ __('filament-appearance::appearance.custom.heading') }}">
                    @foreach ($customPalettes as $record)
                        @php($palette = $record->toPalette())
                        <div class="fap-palette-own" wire:key="palette-{{ $palette->getId() }}">
                            <button
                                type="button"
                                role="radio"
                                aria-checked="{{ $palette->getId() === $current->getId() ? 'true' : 'false' }}"
                                wire:click="selectPalette(@js($palette->getId()))"
                                class="fap-palette-card"
                            >
                                <span class="fap-palette-thumb" style="{{ $thumbnail($palette) }}" aria-hidden="true">
                                    <span class="fap-palette-thumb-bar"><i></i><i></i><i></i></span>
                                    <span class="fap-palette-thumb-page"><span><i></i><i></i><b></b></span></span>
                                </span>
                                <span class="fap-palette-name">{{ $palette->getLabel() }}</span>
                                <span class="fap-palette-note">{{ $palette->getId() === $current->getId() ? __('filament-appearance::appearance.palettes.in_use') : $palette->getDescription() }}</span>
                            </button>

                            <div class="fap-palette-own-actions">
                                {{ ($this->editPaletteAction)(['palette' => $record->getKey()]) }}
                                {{ ($this->deletePaletteAction)(['palette' => $record->getKey()]) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="fap-palette-create">
                {{ $this->createPaletteAction }}
            </div>
        </x-filament::section>
    @endif

    <x-filament-actions::modals />
</div>
