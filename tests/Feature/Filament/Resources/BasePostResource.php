<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\SUpport\Str;
use Tests\Feature\Filament\FilamentBaseTestCase;

class BasePostResource extends FilamentBaseTestCase
{
    use RefreshDatabase;

    protected Collection $posts;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->posts = Post::factory(10)->create(['user_id' => $this->user->getKey()]);

        $this->posts->each(function (Post $post): void {
            $post->addMedia(UploadedFile::fake()->image(Str::random() . '.jpg'))->toMediaCollection('covers');
            $post->categories()->attach(Category::factory(4)->create()->pluck('id'));
        });
    }
}
