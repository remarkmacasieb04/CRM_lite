<?php

namespace App\Policies;

use App\Models\ClientAttachment;
use App\Models\User;

class ClientAttachmentPolicy
{
    public function view(User $user, ClientAttachment $attachment): bool
    {
        return $attachment->user_id === $user->id;
    }

    public function delete(User $user, ClientAttachment $attachment): bool
    {
        return $attachment->user_id === $user->id;
    }
}
