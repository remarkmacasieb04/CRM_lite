<?php

namespace App\Policies;

use App\Models\ClientAttachment;
use App\Models\User;

class ClientAttachmentPolicy
{
    public function view(User $user, ClientAttachment $attachment): bool
    {
        return $attachment->workspace !== null && $user->belongsToWorkspace($attachment->workspace);
    }

    public function delete(User $user, ClientAttachment $attachment): bool
    {
        return $attachment->workspace !== null && $user->belongsToWorkspace($attachment->workspace);
    }
}
