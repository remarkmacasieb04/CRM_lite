<?php

namespace App\Services\Clients;

use App\Enums\ClientActivityType;
use App\Enums\ClientStatus;
use App\Models\User;
use App\Support\ClientData;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientImportService
{
    public function __construct(
        private readonly ClientActivityLogger $clientActivityLogger,
    ) {}

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function import(User $user, UploadedFile $file): array
    {
        $rows = $this->parseAndValidateRows($file);

        return DB::transaction(function () use ($user, $rows): array {
            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($rows as $row) {
                $existingClient = null;

                if (! blank($row['email'])) {
                    $existingClient = $user->clients()
                        ->where('email', $row['email'])
                        ->first();
                }

                if ($existingClient === null) {
                    $client = $user->clients()->create($row);
                    $created++;

                    $this->clientActivityLogger->record(
                        $user,
                        $client,
                        ClientActivityType::Imported,
                        'Imported this client from CSV.',
                    );

                    continue;
                }

                $existingClient->fill($row);
                $changedFields = array_keys($existingClient->getDirty());

                if ($changedFields === []) {
                    $skipped++;

                    continue;
                }

                $existingClient->save();
                $updated++;

                $this->clientActivityLogger->record(
                    $user,
                    $existingClient,
                    ClientActivityType::Imported,
                    'Updated this client from CSV import.',
                    ['changed_fields' => $changedFields],
                );
            }

            return [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseAndValidateRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => ['We could not read that CSV file. Please try again.'],
            ]);
        }

        $headerRow = fgetcsv($handle);

        if ($headerRow === false) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => ['The CSV file is empty. Add a header row and at least one client row.'],
            ]);
        }

        $headers = $this->normalizeHeaders($headerRow);

        if (! in_array('name', $headers, true)) {
            fclose($handle);

            throw ValidationException::withMessages([
                'file' => ['The CSV file must include a name column.'],
            ]);
        }

        $rows = [];
        $errors = [];
        $lineNumber = 1;

        while (($csvRow = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if ($this->rowIsEmpty($csvRow)) {
                continue;
            }

            $mappedRow = $this->mapRow($headers, $csvRow);
            $normalizedRow = ClientData::normalize([
                ...$mappedRow,
                'status' => blank($mappedRow['status'] ?? null)
                    ? ClientStatus::Lead->value
                    : $mappedRow['status'],
            ]);

            $validator = Validator::make($normalizedRow, ClientData::rules());

            if ($validator->fails()) {
                $errors[] = "Row {$lineNumber}: ".collect($validator->errors()->all())->join(' ');

                continue;
            }

            $rows[] = $validator->validated();
        }

        fclose($handle);

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'file' => array_slice($errors, 0, 5),
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => ['No valid client rows were found in the CSV file.'],
            ]);
        }

        return $rows;
    }

    /**
     * @param  list<string|null>  $headers
     * @return list<string|null>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(function (?string $header): ?string {
            if (! is_string($header)) {
                return null;
            }

            $normalized = Str::of($header)
                ->trim()
                ->lower()
                ->replace(['-', ' '], '_')
                ->replaceMatches('/[^a-z0-9_]/', '')
                ->value();

            return match ($normalized) {
                'client_name', 'full_name' => 'name',
                'company_name', 'business' => 'company',
                'last_contacted', 'last_contacted_date' => 'last_contacted_at',
                'follow_up', 'follow_up_date' => 'follow_up_at',
                default => $normalized !== '' ? $normalized : null,
            };
        }, $headers);
    }

    /**
     * @param  list<string|null>  $headers
     * @param  list<string|null>  $values
     * @return array<string, mixed>
     */
    private function mapRow(array $headers, array $values): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            if ($header === null) {
                continue;
            }

            $row[$header] = $values[$index] ?? null;
        }

        return $row;
    }

    /**
     * @param  list<string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (is_string($value) && trim($value) !== '') {
                return false;
            }
        }

        return true;
    }
}
