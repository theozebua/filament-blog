<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use HasTags;
    use InteractsWithMedia;
    use SoftDeletes;

    public static function bootHasTags(): void
    {
        static::created(function (Model $taggableModel) {
            if (count($taggableModel->queuedTags) === 0) {
                return;
            }

            $taggableModel->attachTags($taggableModel->queuedTags);

            $taggableModel->queuedTags = [];
        });

        static::deleted(function (Model $deletedModel) {
            if (method_exists($deletedModel, 'isForceDeleting') && !$deletedModel->isForceDeleting()) {
                return;
            }

            $tags = $deletedModel->tags()->get();

            $deletedModel->detachTags($tags);
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'post_has_categories');
    }

    public function published(): bool
    {
        return !is_null($this->published_at);
    }

    public function archived(): bool
    {
        return !is_null($this->archived_at);
    }

    public function drafted(): bool
    {
        return !$this->published() && !$this->archived() && !$this->trashed();
    }

    public function publish(): self
    {
        $this->update([
            'published_at' => now(),
        ]);

        return $this;
    }

    public function archive(): self
    {
        $this->update([
            'archived_at' => now(),
        ]);

        return $this;
    }

    public function draft(): self
    {
        $this->restore();

        $this->update([
            'published_at' => null,
            'archived_at' => null,
        ]);

        return $this;
    }
}
