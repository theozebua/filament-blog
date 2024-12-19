<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Models\Category;
use App\Models\Post;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\BasePostResource;

class CreatePostTest extends BasePostResource
{
    public function testCanRenderCreatePage(): void
    {
        $this->get(PostResource::getUrl('create'))->assertOk();
    }

    public function testCreatePageHasAForm(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormExists();
    }

    public function testCreatePageHasTitleFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('title');
    }

    public function testCreatePageHasSlugFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('slug');
    }

    public function testCreatePageHasBodyFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('body');
    }

    public function testCreatePageHasCoverFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('cover');
    }

    public function testCreatePageHasCategoriesFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('categories');
    }

    public function testCreatePageHasTagsFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('tags');
    }

    public function testCreatePageHasMetasTitleFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.title');
    }

    public function testCreatePageHasMetasKeywordsFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.keywords');
    }

    public function testCreatePageHasMetasDescriptionFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.description');
    }

    public function testCreatePageHasMetasImageFields(): void
    {
        Livewire::test(CreatePost::class)
            ->assertFormFieldExists('metas.image');
    }

    public function testCreatePageCanAutomaticallyGenerateASlugFromTheTitle(): void
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

    public function testCreatePageCanValidateInput(): void
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

    public function testCreatePageCanCreateAPost(): void
    {
        $post = Post::factory()->makeOne(['user_id' => $this->user->getKey()]);
        $category = Category::factory()->create();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => $title = $post->title,
                'slug' => $slug = str($title)->slug(),
                'body' => $post->body,
                'cover' => [
                    'collection_name' => 'covers',
                    'file_name' => 'something.jpg',
                    'disk' => 'public',
                ],
                'categories' => [
                    'name' => $category->getKey(),
                ],
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
    }
}
