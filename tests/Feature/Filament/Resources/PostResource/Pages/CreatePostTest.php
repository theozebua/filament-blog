<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\BasePostResource;

class CreatePostTest extends BasePostResource
{
    public function testCanRenderCreatePage(): void
    {
        $this->get(PostResource::getUrl('create'))->assertOk();
    }

    public function testHasAForm(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormExists();
    }

    public function testHasTitleField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('title');
    }

    public function testHasSlugField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('slug');
    }

    public function testHasBodyField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('body');
    }

    public function testHasCoverField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('cover');
    }

    public function testHasCategoriesField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('categories');
    }

    public function testHasTagsField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('tags');
    }

    public function testHasMetasTitleField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.title');
    }

    public function testHasMetasKeywordsField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.keywords');
    }

    public function testHasMetasDescriptionField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.description');
    }

    public function testHasMetasImageField(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.image');
    }

    public function testCanAutomaticallyGenerateASlugFromTheTitle(): void
    {
        $title = fake()->sentence();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => $title,
            ])
            ->assertFormSet([
                'slug' => str($title)->slug(),
            ]);
    }

    public function testCanValidateInput(): void
    {
        $post = $this->posts->first();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => $post->title,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'title' => ['unique:posts,title'],
                'slug' => ['unique:posts,slug'],
                'body' => ['required'],
                'cover' => ['required'],
                'categories' => ['required'],
            ]);
    }

    public function testCanCreateAPost(): void
    {
        $post = Post::factory()->makeOne(['user_id' => $this->user->getKey()]);
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('something.jpg');
        $title = $post->title;
        $slug = str($title)->slug();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => $title,
                'slug' => $slug,
                'body' => $post->body,
                'cover' => [$image],
                'categories' => [$category->getKey()],
                'status' => 'published',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Post::class, [
            'user_id' => $this->user->getKey(),
            'title' => $title,
            'slug' => $slug,
            'body' => $post->body,
            'published_at' => now(),
        ]);

        $cover = Post::latest('id')->first()->getFirstMedia('covers');

        Storage::disk('public')->assertExists("{$cover->getKey()}/{$cover->file_name}");
    }
}
