<?php

declare(strict_types=1);

namespace App\Http\Resources\Meta;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'key' => $this->whenHas('key'),
            'value' => $this->whenHas('value'),
            'content' => $this->whenHas('content'),
        ];
    }
}
