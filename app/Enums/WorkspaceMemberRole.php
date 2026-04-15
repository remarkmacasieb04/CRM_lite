<?php

namespace App\Enums;

enum WorkspaceMemberRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Member => 'Member',
        };
    }

    public function canManageWorkspace(): bool
    {
        return in_array($this, [self::Owner, self::Admin], true);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
            ],
            self::cases(),
        );
    }
}
