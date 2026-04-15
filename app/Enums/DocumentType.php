<?php

namespace App\Enums;

enum DocumentType: string
{
    case Proposal = 'proposal';
    case Invoice = 'invoice';

    public function label(): string
    {
        return match ($this) {
            self::Proposal => 'Proposal',
            self::Invoice => 'Invoice',
        };
    }

    public function prefix(): string
    {
        return match ($this) {
            self::Proposal => 'PRO',
            self::Invoice => 'INV',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases(),
        );
    }
}
