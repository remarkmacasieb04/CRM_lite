<?php

namespace App\Enums;

enum SocialProvider: string
{
    case Google = 'google';
    case Facebook = 'facebook';
    case Github = 'github';

    public function label(): string
    {
        return match ($this) {
            self::Google => 'Google',
            self::Facebook => 'Facebook',
            self::Github => 'GitHub',
        };
    }
}
