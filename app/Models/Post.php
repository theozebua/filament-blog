<?php

namespace App\Models;

use App\Enums\Post\PostStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Stringable;
use Spatie\Tags\HasTags;

class Post extends Model
{
    use HasFactory;
    use HasTags;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status' => PostStatus::class,
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public static function bootHasTags(): void
    {
        static::deleted(function (Model $deletedModel) {
            if (method_exists($deletedModel, 'isForceDeleting') && !$deletedModel->isForceDeleting()) {
                return;
            }

            $tags = $deletedModel->tags()->get();

            $deletedModel->detachTags($tags);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cover(): MorphOne
    {
        return $this->morphOne(Cover::class, 'coverable');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function metas(): MorphMany
    {
        return $this->morphMany(Meta::class, 'metaable');
    }

    public function formattedPublishedAt(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->published_at
                ?->locale('id')
                ->settings(['formatFunction' => 'translatedFormat'])
                ->format('l, j F Y h:i'),
        );
    }

    public function scopeOnlyPublished(Builder $query): void
    {
        $query->where('status', PostStatus::PUBLISHED);
    }

    public function scopeSearchableBy(Builder $query, array $columns, string $search): void
    {
        foreach ($columns as $column) {
            $query->where(function (Builder $query) use ($column, $search): void {
                $query->orWhere($column, 'like', "%{$search}%");
            });
        }
    }

    public function scopeWithCategory(Builder $query, Stringable|string $category): void
    {
        $category = $category instanceof Stringable ? $category->value() : $category;

        $query->whereHas('categories', function (Builder $query) use ($category): void {
            $query->where('slug', 'like', "%{$category}%");
        });
    }

    public function scopeWithTag(Builder $query, Stringable|string $tag): void
    {
        $tag = $tag instanceof Stringable ? $tag->value() : $tag;

        $query->whereHas('tags', function (Builder $query) use ($tag): void {
            $query->where('slug', 'like', "%{$tag}%");
        });
    }
}
