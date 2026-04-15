<?php

namespace App\Enums;

enum CommunicationChannel: string
{
    case Email = 'email';
    case Call = 'call';
    case Meeting = 'meeting';
    case Message = 'message';

    public function label(): string
    {
        return match ($this) {
            self::Email => 'Email',
            self::Call => 'Call',
            self::Meeting => 'Meeting',
            self::Message => 'Message',
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $channel): array => [
                'value' => $channel->value,
                'label' => $channel->label(),
            ],
            self::cases(),
        );
    }
}
