<?php

namespace App\Models;

use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'workspace_id',
    'client_id',
    'user_id',
    'channel',
    'direction',
    'subject',
    'summary',
    'happened_at',
])]
class ClientCommunication extends Model
{
    protected function casts(): array
    {
        return [
            'channel' => CommunicationChannel::class,
            'direction' => CommunicationDirection::class,
            'happened_at' => 'immutable_datetime',
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
