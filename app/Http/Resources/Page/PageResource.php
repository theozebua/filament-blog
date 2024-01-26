<?php

declare(strict_types=1);

namespace App\Http\Resources\Page;

use App\Http\Resources\Meta\MetaCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'title' => $this->whenHas('title'),
            'slug' => $this->whenHas('slug'),
            'metas' => MetaCollection::make($this->whenLoaded('metas')),
        ];
    }
}
