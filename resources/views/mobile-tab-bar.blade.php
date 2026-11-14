{{--
    Phone and tablet only (hidden from 1024px up, see resources/css/mobile-tab-bar.css).

    The tab bar has the panel's shortcuts, an optional action in the middle, and "More", which
    opens a sheet with the rest of the menu; the handle at its top closes it, dragged down or
    tapped. The sheet starts with the panel's global search when it has one (it leaves the topbar
    at these widths), or with a plain filter of the pages. The sheet is built from the panel's navigation by
    MortalKiller\FilamentAppearance\Navigation\MobileMenu: open groups are tiles, collapsed groups
    are a row that opens a list. While this bar is on the page the topbar drops its menu button.
--}}
@php
    use Filament\Support\Icons\Heroicon;
    use MortalKiller\FilamentAppearance\View\AppearanceIconAlias;

    use function Filament\Support\generate_icon_html;

    $half = intdiv(count($tabs) + 1, 2);
    $columns = count($tabs) + ($action === null ? 1 : 2);
@endphp

<div
    class="fap-mobile-nav"
    x-data="{
        open: false,
        group: null,
        query: '',
        matches(label) {
            return this.query.trim() === '' || label.toLowerCase().includes(this.query.trim().toLowerCase())
        },
        close() {
            this.open = false
            {{-- The plain filter is emptied; the global search keeps its text, and so does the filter that follows it. --}}
            if (this.$refs.filter) {
                this.query = ''
            }
            this.pull = 0
            this.pullFrom = null
        },
        {{-- The handle: the sheet follows the finger down, and closes past a third of the way or on a tap. --}}
        pull: 0,
        pullFrom: null,
        startPull(event) {
            this.pullFrom = event.clientY
            event.currentTarget.setPointerCapture(event.pointerId)
        },
        movePull(event) {
            if (this.pullFrom !== null) {
                this.pull = Math.max(0, event.clientY - this.pullFrom)
            }
        },
        endPull() {
            if (this.pullFrom === null) {
                return
            }

            const moved = this.pull
            const height = this.$refs.sheet.offsetHeight
            this.pull = 0
            this.pullFrom = null

            if (moved < 5 || moved > Math.min(120, height / 3)) {
                this.close()
            }
        },
    }"
    x-effect="document.body.classList.toggle('fap-mobile-menu-open', open)"
    x-on:keydown.escape.window="close()"
>
    <div class="fap-menu-backdrop" x-show="open" x-transition.opacity x-on:click="close()" x-cloak></div>

    <section
        id="fap-mobile-menu"
        class="fap-menu-sheet"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('filament-appearance::appearance.mobile.menu') }}"
        x-ref="sheet"
        x-show="open"
        x-transition
        x-cloak
        x-bind:data-dragging="pullFrom !== null"
        x-bind:style="pull > 0 ? { transform: 'translateY(' + pull + 'px)' } : {}"
    >
        <button
            type="button"
            class="fap-menu-handle"
            aria-label="{{ __('filament-appearance::appearance.mobile.close') }}"
            x-on:pointerdown="startPull($event)"
            x-on:pointermove="movePull($event)"
            x-on:pointerup="endPull()"
            x-on:pointercancel="endPull()"
            x-on:keydown.enter.prevent="close()"
            x-on:keydown.space.prevent="close()"
        >
            <span class="fap-menu-grabber" aria-hidden="true"></span>
        </button>

        @if ($hasGlobalSearch)
            {{-- One field for both: what is typed searches the records and narrows the pages below. --}}
            <div class="fap-menu-global-search" x-on:input="query = $event.target.value">
                @livewire(\Filament\Livewire\GlobalSearch::class, [], key('fap-mobile-global-search'))
            </div>
        @else
            <label class="fap-menu-search">
                {{ generate_icon_html(Heroicon::OutlinedMagnifyingGlass, alias: AppearanceIconAlias::MOBILE_MENU_SEARCH) }}
                <input
                    type="search"
                    x-ref="filter"
                    x-model="query"
                    placeholder="{{ __('filament-appearance::appearance.mobile.search_placeholder') }}"
                    aria-label="{{ __('filament-appearance::appearance.mobile.search') }}"
                >
            </label>
        @endif

        @foreach ($sections as $section)
            @php($labels = array_column($section['items'], 'label'))

            @if ($section['collapsed'])
                {{-- A collapsed group: one row; its pages open underneath, or show while searching. --}}
                <div
                    class="fap-menu-group"
                    x-show="@js($labels).some((label) => matches(label))"
                >
                    <button
                        type="button"
                        class="fap-menu-row"
                        x-on:click="group = group === @js($section['label']) ? null : @js($section['label'])"
                        x-bind:aria-expanded="group === @js($section['label']) || query.trim() !== ''"
                    >
                        <span>{{ $section['label'] }}</span>
                        <span class="fap-menu-row-end">
                            {{ trans_choice('filament-appearance::appearance.mobile.pages', count($section['items'])) }}
                            {{ generate_icon_html(Heroicon::OutlinedChevronDown, alias: AppearanceIconAlias::MOBILE_MENU_EXPAND) }}
                        </span>
                    </button>

                    <div class="fap-menu-list" x-show="group === @js($section['label']) || query.trim() !== ''" x-cloak>
                        @foreach ($section['items'] as $item)
                            <a
                                href="{{ $item['url'] }}"
                                class="fap-menu-link"
                                x-show="matches(@js($item['label']))"
                                @if ($item['active']) aria-current="page" @endif
                            >
                                {{ generate_icon_html($item['icon']) }}
                                <span>{{ $item['label'] }}</span>
                                @if (filled($item['badge']))
                                    <span class="fap-menu-badge" data-color="{{ $item['badgeColor'] }}">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- An open group: its pages as tiles. --}}
                <div x-show="@js($labels).some((label) => matches(label))">
                    @if ($section['label'] !== null)
                        <h2 class="fap-menu-heading">{{ $section['label'] }}</h2>
                    @endif

                    <div class="fap-menu-tiles">
                        @foreach ($section['items'] as $item)
                            <a
                                href="{{ $item['url'] }}"
                                class="fap-menu-tile"
                                x-show="matches(@js($item['label']))"
                                @if ($item['active']) aria-current="page" @endif
                            >
                                @if (filled($item['badge']))
                                    <span class="fap-menu-badge" data-color="{{ $item['badgeColor'] }}">{{ $item['badge'] }}</span>
                                @endif
                                {{ generate_icon_html($item['icon']) }}
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </section>

    <nav
        class="fap-tab-bar"
        style="--fap-tab-bar-columns: {{ $columns }}"
        aria-label="{{ __('filament-appearance::appearance.mobile.shortcuts') }}"
    >
        @foreach (array_slice($tabs, 0, $half) as $tab)
            <a href="{{ $tab['url'] }}" class="fap-tab-bar-item" @if ($tab['active']) aria-current="page" @endif>
                {{ generate_icon_html($tab['icon']) }}
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach

        @if ($action !== null)
            <div class="fap-tab-bar-action">
                {{ $action instanceof \Illuminate\Contracts\Support\Htmlable ? $action : new \Illuminate\Support\HtmlString($action) }}
            </div>
        @endif

        @foreach (array_slice($tabs, $half) as $tab)
            <a href="{{ $tab['url'] }}" class="fap-tab-bar-item" @if ($tab['active']) aria-current="page" @endif>
                {{ generate_icon_html($tab['icon']) }}
                <span>{{ $tab['label'] }}</span>
            </a>
        @endforeach

        <button
            type="button"
            class="fap-tab-bar-item"
            x-on:click="open ? close() : (open = true)"
            x-bind:aria-expanded="open"
            x-bind:data-open="open"
            aria-controls="fap-mobile-menu"
        >
            <span class="fap-tab-bar-more" x-show="! open">{{ generate_icon_html(Heroicon::OutlinedBars3, alias: AppearanceIconAlias::MOBILE_TAB_BAR_MORE) }}</span>
            <span class="fap-tab-bar-more" x-show="open" x-cloak>{{ generate_icon_html(Heroicon::OutlinedXMark, alias: AppearanceIconAlias::MOBILE_TAB_BAR_CLOSE) }}</span>
            <span x-text="open ? @js(__('filament-appearance::appearance.mobile.close')) : @js(__('filament-appearance::appearance.mobile.more'))">{{ __('filament-appearance::appearance.mobile.more') }}</span>
        </button>
    </nav>
</div>
