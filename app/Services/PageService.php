<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\Page\PageCollection;
use App\Http\Resources\Page\PageResource;
use App\Models\Page;

final class PageService
{
    public function all(): PageCollection
    {
        return PageCollection::make(Page::all());
    }

    public function detail(Page $page): PageResource
    {
        return PageResource::make($page->load(['metas']));
    }
}
