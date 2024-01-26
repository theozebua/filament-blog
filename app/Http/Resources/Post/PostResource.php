<?php

declare(strict_types=1);

namespace App\Http\Resources\Post;

use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Cover\CoverResource;
use App\Http\Resources\Meta\MetaCollection;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'title' => $this->whenHas('title'),
            'slug' => $this->whenHas('slug'),
            'content' => $this->whenHas('content'),
            'published_at' => $this->whenHas('published_at', $this->formattedPublishedAt),
            'cover' => CoverResource::make($this->whenLoaded('cover')),
            'author' => UserResource::make($this->whenLoaded('user')),
            'categories' => CategoryCollection::make($this->whenLoaded('categories')),
            'tags' => TagCollection::make($this->whenLoaded('tags')),
            'metas' => MetaCollection::make($this->whenLoaded('metas')),
        ];
    }
}
