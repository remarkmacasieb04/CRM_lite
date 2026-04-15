<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workspace_id',
    'client_id',
    'user_id',
    'type',
    'title',
    'document_number',
    'status',
    'amount',
    'currency',
    'issued_at',
    'due_at',
    'resolved_at',
    'notes',
    'is_portal_visible',
])]
class ClientDocument extends Model
{
    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'status' => DocumentStatus::class,
            'amount' => 'decimal:2',
            'issued_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'is_portal_visible' => 'boolean',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
