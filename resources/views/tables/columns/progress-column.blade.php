<div class="fap-progress-column">
    @if (blank($getState()) && filled($placeholder = $getPlaceholder()))
        <span class="fap-progress-placeholder">{{ $placeholder }}</span>
    @else
        {{ $getProgressBar() }}
    @endif
</div>
