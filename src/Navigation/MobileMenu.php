<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Navigation;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;

/**
 * The phone's tab bar and its "more" sheet. The sheet is built from the panel's own navigation,
 * so there is no second menu to keep up to date: a new resource or page shows up here by itself,
 * in its group and order.
 *
 * How a group looks is decided where the groups are declared:
 *  - an open group shows its pages as tiles;
 *  - a NavigationGroup::make(…)->collapsed() group is one row that opens the list of its pages.
 */
final class MobileMenu
{
    /**
     * @param  array<NavigationItem>  $items
     * @return list<array{label: string, url: string, icon: string|BackedEnum|Htmlable|null, active: bool}>
     */
    public static function tabs(array $items): array
    {
        $tabs = [];

        foreach ($items as $item) {
            $url = $item->getUrl();

            if ($url === null || ! $item->isVisible()) {
                continue;
            }

            $tabs[] = [
                'label' => (string) $item->getLabel(),
                'url' => $url,
                'icon' => $item->getIcon(),
                'active' => $item->isActive() || request()->url() === $url,
            ];
        }

        return $tabs;
    }

    /**
     * @param  list<string>  $excludedUrls  Pages already on the tab bar, left out of the sheet.
     * @return list<array{
     *     label: string|null,
     *     collapsed: bool,
     *     items: list<array{label: string, url: string, icon: string|BackedEnum|Htmlable|null, badge: string|null, badgeColor: string, active: bool}>
     * }>
     */
    public static function sections(array $excludedUrls = []): array
    {
        $sections = [];

        foreach (Filament::getNavigation() as $group) {
            $items = [];

            foreach ($group->getItems() as $item) {
                $url = $item->getUrl();

                if ($url === null || in_array($url, $excludedUrls, true)) {
                    continue;
                }

                $badge = $item->getBadge();
                $badgeColor = $item->getBadgeColor();

                $items[] = [
                    'label' => (string) $item->getLabel(),
                    'url' => $url,
                    'icon' => $item->getIcon(),
                    'badge' => $badge === null ? null : (string) $badge,
                    'badgeColor' => is_string($badgeColor) ? $badgeColor : 'gray',
                    'active' => $item->isActive(),
                ];
            }

            if ($items === []) {
                continue;
            }

            $label = $group->getLabel();

            $sections[] = [
                'label' => $label === null ? null : (string) $label,
                // Pages outside any group have no row to hide behind: always tiles.
                'collapsed' => $label !== null && $group->isCollapsed(),
                'items' => $items,
            ];
        }

        return $sections;
    }
}
