<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;

final class CategoryService
{
    public function all(): CategoryCollection
    {
        return CategoryCollection::make(
            Category::withCount(['posts' => fn ($query) => $query->onlyPublished()])->get(),
        );
    }

    public function detail(Category $category): CategoryResource
    {
        return CategoryResource::make($category);
    }
}
