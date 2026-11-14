<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Tests\Fixtures;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MortalKiller\FilamentAppearance\AppearancePlugin;
use MortalKiller\FilamentAppearance\Palette;

/** A panel that uses every part of the plugin. */
final class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('test')
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->pages([Home::class, Reports::class, Settings::class])
            ->navigationGroups([
                NavigationGroup::make('Analysis'),
                NavigationGroup::make('System')->collapsed(),
            ])
            ->plugins([
                AppearancePlugin::make()
                    ->palettes([
                        Palette::make('forest')
                            ->label('Forest')
                            ->description('Deep green')
                            ->primary('#18b980')
                            ->action('#0a5b42')
                            ->background('#e5f3f1')
                            ->brandLogo('/logos/forest.svg'),
                        Palette::make('paper')
                            ->primary('#2563eb')
                            ->navigation('#ffffff', dark: '#111827'),
                        Palette::make('ink')
                            ->primary('#2563eb')
                            ->navigation('#111827')
                            ->brandLogo('/logos/ink-light.svg', dark: '/logos/ink-dark.svg')
                            ->collapsedBrandLogo('/logos/ink-symbol.svg'),
                    ])
                    ->defaultPalette('forest')
                    ->customPalettes(limit: 2)
                    ->brandLogo('/logos/on-light.svg', dark: '/logos/on-dark.svg')
                    ->collapsedBrandLogo('/logos/symbol-on-light.svg', dark: '/logos/symbol-on-dark.svg')
                    ->topbarSearchAtStart()
                    ->underlinedTabs()
                    ->mobileTables()
                    ->mobileEdgeToEdge()
                    ->mobileTabBar()
                    ->mobileTabBarItems(fn (): array => [
                        NavigationItem::make('Home')->icon(Heroicon::OutlinedHome)->url(Home::getUrl()),
                    ])
                    ->mobileTabBarAction('<button class="new-record">New</button>')
                    ->appearancePage(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
