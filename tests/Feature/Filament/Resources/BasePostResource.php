<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Filament\FilamentBaseTestCase;

class BasePostResource extends FilamentBaseTestCase
{
    use RefreshDatabase;

    protected Collection $posts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posts = Post::factory(10)->create(['user_id' => $this->user->getKey()]);
    }
}
