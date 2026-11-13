<?php

namespace App\Support;

/**
 * What deleting one record would do, built by a repository's deletionImpact():
 *
 *  - blockers: other records that depend on it. While any exist the delete is refused, so
 *    nothing is left pointing at a deleted record and no stock / ledger history is lost.
 *  - cascades: rows that belong to the record and are deleted with it (its items, images,
 *    its own ledger entries...). Listed in the confirmation so the user knows what goes.
 *  - notes:    other effects worth knowing (e.g. "issued stock returns to general stock").
 *
 * The delete button asks the server for this (GET .../delete-check) before confirming, and the
 * destroy action builds it again and refuses when blocked, so the check cannot be skipped.
 */
class DeletionImpact
{
    private array $blockers = [];

    private array $cascades = [];

    private array $notes = [];

    public function __construct(public readonly string $label)
    {
    }

    public static function for(string $label): self
    {
        return new self($label);
    }

    /**
     * Adds a blocker when $count > 0, e.g. blockIf(3, 'delivery', 'deliveries', ['SLE-D001-26', ...]).
     */
    public function blockIf(int $count, string $singular, ?string $plural = null, iterable $examples = []): self
    {
        if ($count > 0) {
            $this->blockers[] = $this->phrase($count, $singular, $plural, $examples);
        }

        return $this;
    }

    public function block(string $reason): self
    {
        $this->blockers[] = $reason;

        return $this;
    }

    /**
     * Adds a cascade line when $count > 0, e.g. cascadeIf(5, 'order item').
     */
    public function cascadeIf(int $count, string $singular, ?string $plural = null, iterable $examples = []): self
    {
        if ($count > 0) {
            $this->cascades[] = $this->phrase($count, $singular, $plural, $examples);
        }

        return $this;
    }

    public function note(string $note): self
    {
        $this->notes[] = $note;

        return $this;
    }

    public function isBlocked(): bool
    {
        return ! empty($this->blockers);
    }

    public function hasDependents(): bool
    {
        return ! empty($this->cascades);
    }

    public function blockers(): array
    {
        return $this->blockers;
    }

    /**
     * One-line reason for a flash message when the delete is refused.
     */
    public function blockedMessage(): string
    {
        return $this->label.' cannot be deleted because it is used by: '.implode('; ', $this->blockers)
            .'. Delete or change those records first.';
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'can_delete' => ! $this->isBlocked(),
            'blockers' => $this->blockers,
            'cascades' => $this->cascades,
            'notes' => $this->notes,
        ];
    }

    private function phrase(int $count, string $singular, ?string $plural, iterable $examples): string
    {
        $noun = $count === 1 ? $singular : ($plural ?? $singular.'s');
        $examples = collect($examples)->filter(fn ($value) => (string) $value !== '')->map(fn ($value) => (string) $value)->unique()->values();
        $text = $count.' '.$noun;
        if ($examples->isNotEmpty()) {
            $shown = $examples->take(5)->implode(', ');
            $text .= ' ('.$shown.($examples->count() > 5 || $count > 5 ? ', ...' : '').')';
        }

        return $text;
    }
}
