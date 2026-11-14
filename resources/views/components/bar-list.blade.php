{{-- MortalKiller\FilamentAppearance\Support\BarList. Styles: resources/css/bar-list.css. --}}
<ul @class(['fap-bar-list', 'fap-bar-list-strong' => $hasStrongValues])>
    @foreach ($items as $item)
        <li class="fap-bar-list-item">
            <span class="fap-bar-list-head">
                <span class="fap-bar-list-label">{{ $item['label'] }}</span>
                <span class="fap-bar-list-value">{{ $item['value'] }}</span>
            </span>
            <span class="fap-progress" style="--fap-progress-color: var(--{{ $color }}-500)"><i style="width: {{ $item['width'] }}%"></i></span>
            @if (filled($item['note']))
                <span class="fap-bar-list-note" {!! filled($item['noteColor']) ? 'style="--fap-bar-list-note-color: var(--'.e($item['noteColor']).'-700); --fap-bar-list-note-color-dark: var(--'.e($item['noteColor']).'-400)"' : '' !!}>{{ $item['note'] }}</span>
            @endif
        </li>
    @endforeach
</ul>
