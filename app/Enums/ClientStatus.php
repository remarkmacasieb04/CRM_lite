<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Lead = 'lead';
    case ProposalSent = 'proposal_sent';
    case Active = 'active';
    case Waiting = 'waiting';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Lead => 'Lead',
            self::ProposalSent => 'Proposal Sent',
            self::Active => 'Active',
            self::Waiting => 'Waiting',
            self::Completed => 'Completed',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases(),
        );
    }
}
