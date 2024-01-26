<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Page\PageCollection;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function __construct(protected readonly PageService $pageService)
    {
        //
    }

    public function index(): PageCollection
    {
        return $this->pageService->all();
    }

    public function show(Page $page): JsonResponse
    {
        return new JsonResponse([
            'data' => $this->pageService->detail($page),
        ]);
    }
}
