<?php

namespace App\Support;

use App\Enums\ClientStatus;
use Illuminate\Validation\Rule;

class ClientFilters
{
    public const PERSISTABLE_KEYS = [
        'search',
        'status',
        'archived',
        'tag',
        'follow_up',
        'stale',
    ];

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        return [
            ...$input,
            'search' => self::normalizeString($input['search'] ?? null),
            'status' => self::normalizeString($input['status'] ?? null),
            'archived' => self::normalizeString($input['archived'] ?? null),
            'tag' => self::normalizeString($input['tag'] ?? null),
            'follow_up' => self::normalizeString($input['follow_up'] ?? null),
            'stale' => self::normalizeString($input['stale'] ?? null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::enum(ClientStatus::class)],
            'archived' => ['nullable', Rule::in(['only'])],
            'tag' => ['nullable', 'string', 'max:120'],
            'follow_up' => ['nullable', Rule::in(['overdue', 'week'])],
            'stale' => ['nullable', Rule::in(['yes'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function active(array $input): array
    {
        return collect(self::normalize($input))
            ->only(self::PERSISTABLE_KEYS)
            ->filter(fn (mixed $value): bool => $value !== null && $value !== '')
            ->all();
    }

    /**
     * @param  array<string, mixed>  $current
     * @param  array<string, mixed>  $candidate
     */
    public static function matches(array $current, array $candidate): bool
    {
        return self::active($current) === self::active($candidate);
    }

    private static function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
