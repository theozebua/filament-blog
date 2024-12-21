<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Filament\FilamentBaseTestCase;

class BaseCategoryResource extends FilamentBaseTestCase
{
    use RefreshDatabase;

    protected Collection $categories;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Category::factory(10)->create();
    }
}
