<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Post\PostCollection;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(protected readonly PostService $postService)
    {
        //
    }

    public function index(Request $request): PostCollection
    {
        return $this->postService->all(
            columns: ['id', 'title', 'slug', 'content', 'published_at', 'user_id'],
            relations: ['user', 'cover', 'categories', 'tags', 'metas'],
            search: $request->query('search', ''),
            category: $request->str('category', ''),
            tag: $request->str('tag', ''),
            perPage: $request->integer('per_page', null),
        );
    }

    public function show(Post $post): JsonResponse
    {
        return new JsonResponse([
            'data' => PostResource::make($this->postService->detail($post)),
        ]);
    }
}
