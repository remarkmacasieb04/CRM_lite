<?php

namespace App\Services\Clients;

use App\Enums\DocumentType;
use App\Models\ClientDocument;
use App\Models\Workspace;

class ClientDocumentNumberGenerator
{
    public function generate(Workspace $workspace, DocumentType $type): string
    {
        $nextNumber = ClientDocument::query()
            ->whereBelongsTo($workspace)
            ->where('type', $type->value)
            ->count() + 1;

        return sprintf('%s-%04d', $type->prefix(), $nextNumber);
    }
}
