<?php

namespace App\Support;

use App\Enums\ClientStatus;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientData
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        return [
            ...$input,
            'name' => self::normalizeString($input['name'] ?? null),
            'company' => self::normalizeString($input['company'] ?? null),
            'email' => self::normalizeEmail($input['email'] ?? null),
            'phone' => self::normalizeString($input['phone'] ?? null),
            'status' => self::normalizeStatus($input['status'] ?? null),
            'budget' => self::normalizeString($input['budget'] ?? null),
            'source' => self::normalizeString($input['source'] ?? null),
            'last_contacted_at' => self::normalizeString($input['last_contacted_at'] ?? null),
            'follow_up_at' => self::normalizeString($input['follow_up_at'] ?? null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'status' => ['required', Rule::enum(ClientStatus::class)],
            'budget' => ['nullable', 'decimal:0,2', 'min:0'],
            'source' => ['nullable', 'string', 'max:120'],
            'last_contacted_at' => ['nullable', 'date'],
            'follow_up_at' => ['nullable', 'date'],
        ];
    }

    private static function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function normalizeEmail(mixed $value): ?string
    {
        $email = self::normalizeString($value);

        return $email === null ? null : mb_strtolower($email);
    }

    private static function normalizeStatus(mixed $value): ?string
    {
        $status = self::normalizeString($value);

        if ($status === null) {
            return null;
        }

        foreach (ClientStatus::cases() as $case) {
            if ($status === $case->value) {
                return $case->value;
            }

            if (Str::lower($status) === Str::lower($case->label())) {
                return $case->value;
            }
        }

        return Str::of($status)
            ->lower()
            ->replace(['-', ' '], '_')
            ->value();
    }
}
