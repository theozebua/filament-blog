<?php

declare(strict_types=1);

namespace App\Http\Resources\Meta;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MetaCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
