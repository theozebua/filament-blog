<?php

declare(strict_types=1);

namespace App\Http\Resources\Cover;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'title' => $this->whenHas('title'),
            'alt' => $this->whenHas('alt'),
            'path' => 'storage/' . $this->whenHas('path'),
        ];
    }
}
