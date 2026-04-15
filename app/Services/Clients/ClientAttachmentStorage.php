<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\ClientAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ClientAttachmentStorage
{
    public function store(User $user, Client $client, UploadedFile $uploadedFile): ClientAttachment
    {
        $path = $uploadedFile->store(
            "client-attachments/{$user->id}/{$client->id}",
            'local',
        );

        return $client->attachments()->create([
            'user_id' => $user->id,
            'workspace_id' => $client->workspace_id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);
    }

    public function delete(ClientAttachment $attachment): void
    {
        if (Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();
    }

    public function purgeForClient(Client $client): void
    {
        $client->loadMissing('attachments');

        foreach ($client->attachments as $attachment) {
            $this->delete($attachment);
        }
    }
}
