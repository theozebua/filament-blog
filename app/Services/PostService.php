<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Post\PostStatus;
use App\Http\Resources\Post\PostCollection;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Illuminate\Support\Stringable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PostService
{
    public function all(
        array $columns = ['*'],
        array $relations = [],
        string $search = '',
        Stringable|string $category = '',
        Stringable|string $tag = '',
        ?int $perPage = null,
    ): PostCollection {
        return PostCollection::make(
            Post::with($relations)
                ->onlyPublished()
                ->searchableBy(['title', 'content'], $search)
                ->withCategory($category)
                ->withTag($tag)
                ->latest()
                ->paginate($perPage, $columns),
        );
    }

    public function detail(Post $post): PostResource
    {
        if ($post->status !== PostStatus::PUBLISHED) {
            throw new NotFoundHttpException();
        }

        return PostResource::make($post->load(['user', 'cover', 'categories', 'tags', 'metas']));
    }
}
