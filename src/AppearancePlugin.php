<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance;

use Closure;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Pages\SimplePage;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Livewire;
use MortalKiller\FilamentAppearance\Facades\Appearance;
use MortalKiller\FilamentAppearance\Http\Middleware\ApplyPalette;
use MortalKiller\FilamentAppearance\Navigation\MobileMenu;
use MortalKiller\FilamentAppearance\Pages\Appearance as AppearancePage;
use MortalKiller\FilamentAppearance\View\AppearanceIconAlias;

final class AppearancePlugin implements Plugin
{
    use EvaluatesClosures;

    public const ID = 'filament-appearance';

    /** @var array<Palette>|Closure */
    private array|Closure $palettes = [];

    private string|Closure|null $defaultPalette = null;

    private bool|Closure $hasCustomPalettes = false;

    private int $customPalettesLimit = 12;

    private string|Htmlable|Closure|null $brandLogo = null;

    private string|Htmlable|Closure|null $darkModeBrandLogo = null;

    private string|Htmlable|Closure|null $collapsedBrandLogo = null;

    private string|Htmlable|Closure|null $darkModeCollapsedBrandLogo = null;

    private string|Closure|null $brandLogoHeight = null;

    private bool|Closure $hasTopbarSearchAtStart = false;

    private bool|Closure $hasTableSearchAtStart = false;

    private bool|Closure $hasUnderlinedTabs = false;

    private bool|Closure $hasMobileTables = false;

    private bool|Closure $hasMobileEdgeToEdge = false;

    private bool|Closure $hasMobileTabBar = false;

    /** @var array<NavigationItem>|Closure */
    private array|Closure $mobileTabBarItems = [];

    private string|Htmlable|Closure|null $mobileTabBarAction = null;

    private bool $hasAppearancePage = false;

    public function getId(): string
    {
        return self::ID;
    }

    public static function make(): static
    {
        return app(self::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(self::ID);

        return $plugin;
    }

    /**
     * The palettes a user can pick. The first one is the default unless defaultPalette() says
     * otherwise.
     *
     * @param  array<Palette>|Closure  $palettes
     */
    public function palettes(array|Closure $palettes): static
    {
        $this->palettes = $palettes;

        return $this;
    }

    /** The id of the palette used by guests and by users who have not picked one. */
    public function defaultPalette(string|Closure|null $id): static
    {
        $this->defaultPalette = $id;

        return $this;
    }

    /** Lets users make their own palettes from three colours, up to $limit each. */
    public function customPalettes(bool|Closure $condition = true, int $limit = 12): static
    {
        $this->hasCustomPalettes = $condition;
        $this->customPalettesLimit = $limit;

        return $this;
    }

    /**
     * The panel's logo: $logo on light surfaces, $dark on dark ones (the same logo when $dark is
     * not given). Used on the sign-in pages, and on the navigation bar of every palette that has
     * no logo of its own, where the bar's colour decides which of the two is shown.
     */
    public function brandLogo(string|Htmlable|Closure|null $logo, string|Htmlable|Closure|null $dark = null): static
    {
        $this->brandLogo = $logo;
        $this->darkModeBrandLogo = $dark;

        return $this;
    }

    /**
     * The compact logo for a collapsed sidebar (a panel with sidebarCollapsibleOnDesktop()):
     * $logo on a light navigation bar, $dark on a dark one (the same when $dark is not given).
     * A palette's own collapsedBrandLogo() wins over these.
     */
    public function collapsedBrandLogo(string|Htmlable|Closure|null $logo, string|Htmlable|Closure|null $dark = null): static
    {
        $this->collapsedBrandLogo = $logo;
        $this->darkModeCollapsedBrandLogo = $dark;

        return $this;
    }

    public function brandLogoHeight(string|Closure|null $height): static
    {
        $this->brandLogoHeight = $height;

        return $this;
    }

    /** On desktop, the global search sits at the start of the topbar, next to the sidebar. */
    public function topbarSearchAtStart(bool|Closure $condition = true): static
    {
        $this->hasTopbarSearchAtStart = $condition;

        return $this;
    }

    /** In every table, the search sits at the start of the toolbar and the toolbar actions at its end. */
    public function tableSearchAtStart(bool|Closure $condition = true): static
    {
        $this->hasTableSearchAtStart = $condition;

        return $this;
    }

    /** Tabs as a row of labels with a line under the active one, instead of a floating card. */
    public function underlinedTabs(bool|Closure $condition = true): static
    {
        $this->hasUnderlinedTabs = $condition;

        return $this;
    }

    /** On a phone, tables become lists of cards instead of scrolling sideways. */
    public function mobileTables(bool|Closure $condition = true): static
    {
        $this->hasMobileTables = $condition;

        return $this;
    }

    /** On a phone, sections, tables and stats go from one edge of the screen to the other. */
    public function mobileEdgeToEdge(bool|Closure $condition = true): static
    {
        $this->hasMobileEdgeToEdge = $condition;

        return $this;
    }

    /**
     * On a phone and a tablet, a bar of shortcuts at the bottom of the screen. Its last button
     * opens a sheet with the rest of the panel's navigation and, when the panel has one, its
     * global search, which leaves the topbar at those widths.
     */
    public function mobileTabBar(bool|Closure $condition = true): static
    {
        $this->hasMobileTabBar = $condition;

        return $this;
    }

    /**
     * The shortcuts on the tab bar. An item is active when its URL is the current one, or when
     * its own isActiveWhen() says so.
     *
     * @param  array<NavigationItem>|Closure  $items
     */
    public function mobileTabBarItems(array|Closure $items): static
    {
        $this->mobileTabBarItems = $items;

        return $this;
    }

    /** What sits in the middle of the tab bar, e.g. a round "new" button. */
    public function mobileTabBarAction(string|Htmlable|Closure|null $action): static
    {
        $this->mobileTabBarAction = $action;

        return $this;
    }

    /** Adds an "Appearance" page with the palette picker, and a link to it in the user menu. */
    public function appearancePage(bool $condition = true): static
    {
        $this->hasAppearancePage = $condition;

        return $this;
    }

    /**
     * @return array<string, Palette>
     */
    public function getPalettes(): array
    {
        $palettes = [];

        foreach ($this->evaluate($this->palettes) as $palette) {
            if (! $palette instanceof Palette) {
                throw new InvalidArgumentException('AppearancePlugin::palettes() expects Palette instances.');
            }

            $palettes[$palette->getId()] = $palette;
        }

        return $palettes;
    }

    public function getDefaultPalette(): Palette
    {
        $palettes = $this->getPalettes();
        $id = $this->evaluate($this->defaultPalette);

        if ($id !== null && ! isset($palettes[$id])) {
            throw new InvalidArgumentException("The default palette [{$id}] is not one of the registered palettes.");
        }

        return $palettes[$id ?? array_key_first($palettes)] ?? Palette::make('default');
    }

    public function hasCustomPalettes(): bool
    {
        return (bool) $this->evaluate($this->hasCustomPalettes);
    }

    public function getCustomPalettesLimit(): int
    {
        return $this->customPalettesLimit;
    }

    public function hasTopbarSearchAtStart(): bool
    {
        return (bool) $this->evaluate($this->hasTopbarSearchAtStart);
    }

    public function hasTableSearchAtStart(): bool
    {
        return (bool) $this->evaluate($this->hasTableSearchAtStart);
    }

    public function hasUnderlinedTabs(): bool
    {
        return (bool) $this->evaluate($this->hasUnderlinedTabs);
    }

    public function hasMobileTables(): bool
    {
        return (bool) $this->evaluate($this->hasMobileTables);
    }

    public function hasMobileEdgeToEdge(): bool
    {
        return (bool) $this->evaluate($this->hasMobileEdgeToEdge);
    }

    public function hasMobileTabBar(): bool
    {
        return (bool) $this->evaluate($this->hasMobileTabBar);
    }

    public function hasAppearancePage(): bool
    {
        return $this->hasAppearancePage;
    }

    /**
     * @return array<NavigationItem>
     */
    public function getMobileTabBarItems(): array
    {
        return $this->evaluate($this->mobileTabBarItems);
    }

    public function getMobileTabBarAction(): string|Htmlable|null
    {
        return $this->evaluate($this->mobileTabBarAction);
    }

    /**
     * The shell features that are on, as the words the stylesheet looks for in the
     * "filament-appearance" meta tag.
     *
     * @return list<string>
     */
    public function getFeatures(): array
    {
        return array_keys(array_filter([
            'search' => $this->hasTopbarSearchAtStart(),
            'tablesearch' => $this->hasTableSearchAtStart(),
            'tabs' => $this->hasUnderlinedTabs(),
            'tables' => $this->hasMobileTables(),
            'edge' => $this->hasMobileEdgeToEdge(),
        ]));
    }

    /**
     * The logo for the current page. On the navigation bar the palette's own logo wins; otherwise
     * the bar's colour picks between the panel's two logos. Sign-in pages have a plain surface.
     */
    public function getBrandLogo(bool $dark = false): string|Htmlable|null
    {
        $onLightSurface = $this->evaluate($this->brandLogo);
        $onDarkSurface = $this->evaluate($this->darkModeBrandLogo) ?? $onLightSurface;

        if (self::isOnSimplePage()) {
            return $dark ? $onDarkSurface : $onLightSurface;
        }

        $palette = Appearance::palette();

        if ($palette->hasBrandLogo()) {
            return $palette->getBrandLogo($dark);
        }

        return $palette->hasLightNavigation($dark) ? $onLightSurface : $onDarkSurface;
    }

    /**
     * The compact logo for a collapsed sidebar: the palette's own, or the one of the panel's two
     * that reads on the navigation bar. Null when none was given.
     */
    public function getCollapsedBrandLogo(bool $dark = false): string|Htmlable|null
    {
        $palette = Appearance::palette();

        if ($palette->hasCollapsedBrandLogo()) {
            return $palette->getCollapsedBrandLogo($dark);
        }

        $onLightSurface = $this->evaluate($this->collapsedBrandLogo);
        $onDarkSurface = $this->evaluate($this->darkModeCollapsedBrandLogo) ?? $onLightSurface;

        return $palette->hasLightNavigation($dark) ? $onLightSurface : $onDarkSurface;
    }

    public function getBrandLogoHeight(): ?string
    {
        $height = self::isOnSimplePage() ? null : Appearance::palette()->getBrandLogoHeight();

        return $height ?? $this->evaluate($this->brandLogoHeight);
    }

    public function register(Panel $panel): void
    {
        $panel
            ->authMiddleware([ApplyPalette::class], isPersistent: true)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament-appearance::head', [
                    'palette' => Appearance::palette(),
                    // "tabbar" tells the stylesheet, from the first paint, that this page ends with the tab bar.
                    'features' => [...$this->getFeatures(), ...($this->showsMobileTabBar() ? ['tabbar'] : [])],
                ])->render(),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_LOGO_AFTER,
                fn (): string => $this->renderCollapsedBrandLogo($panel),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => $this->hasMobileTables() ? view('filament-appearance::mobile-tables')->render() : '',
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => $this->renderMobileTabBar(),
            );

        if ($this->hasLogos()) {
            $panel
                ->brandLogo(fn (): string|Htmlable|null => $this->getBrandLogo())
                ->darkModeBrandLogo(fn (): string|Htmlable|null => $this->getBrandLogo(dark: true));
        }

        if ($this->hasLogoHeights()) {
            $panel->brandLogoHeight(fn (): ?string => $this->getBrandLogoHeight());
        }

        if ($this->hasAppearancePage) {
            $panel
                ->pages([AppearancePage::class])
                ->userMenuItems([
                    'appearance' => Action::make('appearance')
                        ->label(fn (): string => __('filament-appearance::appearance.page.title'))
                        ->icon(FilamentIcon::resolve(AppearanceIconAlias::USER_MENU_APPEARANCE) ?? Heroicon::OutlinedSwatch)
                        ->url(fn (): string => AppearancePage::getUrl()),
                ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Sign-in, sign-up and the like: a plain surface with no navigation bar. The page is read
     * from the route on a page load, and from the component on its Livewire updates.
     */
    public static function isOnSimplePage(): bool
    {
        if (Livewire::current() instanceof SimplePage) {
            return true;
        }

        $action = request()->route()?->getAction('uses');
        $page = is_string($action) ? Str::before($action, '@') : null;

        return $page !== null && is_subclass_of($page, SimplePage::class);
    }

    private function renderCollapsedBrandLogo(Panel $panel): string
    {
        if (! $panel->isSidebarCollapsibleOnDesktop()) {
            return '';
        }

        $logo = $this->getCollapsedBrandLogo();

        if ($logo === null) {
            return '';
        }

        $brandName = $panel->getBrandName();

        return view('filament-appearance::collapsed-logo', [
            'logo' => $logo,
            'darkLogo' => $this->getCollapsedBrandLogo(dark: true) ?? $logo,
            'label' => strip_tags($brandName instanceof Htmlable ? $brandName->toHtml() : $brandName),
            'url' => $panel->getHomeUrl(),
        ])->render();
    }

    /** For whoever is signed in, on a page of the panel: never on the sign-in pages. */
    private function showsMobileTabBar(): bool
    {
        return ! self::isOnSimplePage() && Filament::auth()->check() && $this->hasMobileTabBar();
    }

    private function renderMobileTabBar(): string
    {
        if (! $this->showsMobileTabBar()) {
            return '';
        }

        $items = MobileMenu::tabs($this->getMobileTabBarItems());

        return view('filament-appearance::mobile-tab-bar', [
            'tabs' => $items,
            'action' => $this->getMobileTabBarAction(),
            'sections' => MobileMenu::sections(excludedUrls: array_column($items, 'url')),
            'hasGlobalSearch' => Filament::isGlobalSearchEnabled(),
        ])->render();
    }

    /** Palettes given as a closure are only known per request, so they do not count here. */
    private function hasLogos(): bool
    {
        if ($this->brandLogo !== null) {
            return true;
        }

        foreach (is_array($this->palettes) ? $this->palettes : [] as $palette) {
            if ($palette->hasBrandLogo()) {
                return true;
            }
        }

        return false;
    }

    private function hasLogoHeights(): bool
    {
        if ($this->brandLogoHeight !== null) {
            return true;
        }

        foreach (is_array($this->palettes) ? $this->palettes : [] as $palette) {
            if ($palette->getBrandLogoHeight() !== null) {
                return true;
            }
        }

        return false;
    }
}
