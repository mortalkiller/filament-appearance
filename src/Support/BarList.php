<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Support;

use Illuminate\Contracts\Support\Htmlable;

/**
 * A ranked list of labelled bars, the biggest first: "who owes the most", "where the money
 * goes". Each bar is as long as its amount relative to the largest one; the figure shown beside
 * the label is whatever you pass (a formatted amount, a share).
 *
 *     BarList::make()
 *         ->item('Cloud Demo', '52%', 20000)
 *         ->item('Loja Demo', '€61.50', 6150, note: '€61.50 overdue', noteColor: 'danger')
 *         ->color('warning');
 */
final class BarList implements Htmlable
{
    /** @var list<array{label: string, value: string, amount: int|float, note: ?string, noteColor: ?string}> */
    private array $items = [];

    private string $color = 'primary';

    private bool $hasStrongValues = false;

    private function __construct() {}

    public static function make(): self
    {
        return new self;
    }

    /**
     * @param  int|float  $amount  What the bar's length compares; it is never shown.
     * @param  string|null  $noteColor  A Filament colour name for the note, or null for a muted one.
     */
    public function item(string $label, string $value, int|float $amount, ?string $note = null, ?string $noteColor = null): self
    {
        $this->items[] = ['label' => $label, 'value' => $value, 'amount' => $amount, 'note' => $note, 'noteColor' => $noteColor];

        return $this;
    }

    /** Any colour registered on the panel, its own ones included. */
    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /** The figures in semibold, when they are the point of the list (amounts owed). */
    public function strongValues(bool $condition = true): self
    {
        $this->hasStrongValues = $condition;

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function toHtml(): string
    {
        $largest = max([0, ...array_map(fn (array $item): int|float => $item['amount'], $this->items)]);

        return view('filament-appearance::components.bar-list', [
            'items' => array_map(fn (array $item): array => [
                ...$item,
                'width' => $largest > 0 ? round(max(0, $item['amount']) / $largest * 100, 1) : 0,
            ], $this->items),
            'color' => $this->color,
            'hasStrongValues' => $this->hasStrongValues,
        ])->render();
    }
}
