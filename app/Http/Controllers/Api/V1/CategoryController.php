<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryCollection;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(protected readonly CategoryService $categoryService)
    {
        //
    }

    public function index(): CategoryCollection
    {
        return $this->categoryService->all();
    }

    public function show(Category $category): JsonResponse
    {
        return new JsonResponse([
            'data' => $this->categoryService->detail($category),
        ]);
    }
}
