<?php

namespace App\Models;

use App\Enums\ClientActivityType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'client_id',
    'type',
    'description',
    'properties',
])]
class ClientActivity extends Model
{
    protected function casts(): array
    {
        return [
            'type' => ClientActivityType::class,
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
