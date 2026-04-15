<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;

class ClientTagSyncService
{
    public function sync(User $user, Client $client, ?string $tagList): void
    {
        $tagNames = collect(explode(',', (string) $tagList))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => Str::lower($tag))
            ->values();

        if ($tagNames->isEmpty()) {
            $client->tags()->sync([]);

            return;
        }

        $tagIds = $tagNames->map(function (string $name) use ($user): int {
            $tag = Tag::query()->firstOrCreate(
                [
                    'workspace_id' => $user->current_workspace_id,
                    'slug' => Str::slug($name),
                ],
                [
                    'user_id' => $user->id,
                    'name' => $name,
                ],
            );

            if ($tag->name !== $name) {
                $tag->forceFill(['name' => $name])->save();
            }

            return $tag->id;
        });

        $client->tags()->sync($tagIds->all());
    }
}
