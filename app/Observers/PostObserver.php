<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Storage;

class PostObserver implements ShouldHandleEventsAfterCommit
{
    public function deleting(Post $post): void
    {
        $post->update([
            'published_at' => null,
            'archived_at' => null,
        ]);
    }

    public function forceDeleted(Post $post): void
    {
        $oldImage = $post->metas->where('key', 'image')->first();

        $storage = Storage::disk('public');

        $storage->exists($oldImage->value ?? '') && $storage->delete($oldImage->value ?? '');

        $post->metas->each->delete();
    }
}
