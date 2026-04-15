<?php

namespace App\Enums;

enum CommunicationDirection: string
{
    case Outbound = 'outbound';
    case Inbound = 'inbound';
    case Internal = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::Outbound => 'Outbound',
            self::Inbound => 'Inbound',
            self::Internal => 'Internal',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $direction): array => [
                'value' => $direction->value,
                'label' => $direction->label(),
            ],
            self::cases(),
        );
    }
}
