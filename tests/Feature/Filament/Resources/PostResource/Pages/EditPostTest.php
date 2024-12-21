<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\EditPost;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\BasePostResource;

class EditPostTest extends BasePostResource
{
    protected Post $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->record = $this->posts->first();
    }

    public function testCanRenderEditPage(): void
    {
        $this->get(PostResource::getUrl('edit', [
            'record' => $this->record,
        ]))->assertOk();
    }

    public function testHasAForm(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormExists();
    }

    public function testHasTitleField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('title');
    }

    public function testHasSlugField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('slug');
    }

    public function testHasBodyField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('body');
    }

    public function testHasCoverField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('cover');
    }

    public function testHasCategoriesField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('categories');
    }

    public function testHasTagsField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('tags');
    }

    public function testHasMetasTitleField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('metas.title');
    }

    public function testHasMetasKeywordsField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('metas.keywords');
    }

    public function testHasMetasDescriptionField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('metas.description');
    }

    public function testHasMetasImageField(): void
    {
        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('metas.image');
    }

    public function testCanAutomaticallyGenerateASlugFromTheTitle(): void
    {
        $title = fake()->sentence();

        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->fillForm([
                'title' => $title,
            ])
            ->assertFormSet([
                'slug' => str($title)->slug(),
            ]);
    }

    public function testCanValidateInput(): void
    {
        $post = $this->posts->sortByDesc('id')->first();

        Livewire::test(EditPost::class, ['record' => $this->record->getRouteKey()])
            ->fillForm([
                'title' => $post->title,
                'body' => null,
                'cover' => null,
                'categories' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'title' => ['unique:posts,title'],
                'slug' => ['unique:posts,slug'],
                'body' => ['required'],
                'cover' => ['required'],
                'categories' => ['required'],
            ]);
    }

    public function testCanRetrievePost(): void
    {
        /** @var Post $post */
        $post = $this->posts->first();

        $media = $post->getFirstMedia('covers');

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->assertFormSet([
                'title' => $post->title,
                'slug' => $post->slug,
                'body' => $post->body,
                'cover' => [
                    $media->uuid => $media->uuid,
                ],
                'categories' => $post->categories->pluck('id')->toArray(),
            ]);
    }

    public function testCanUpdateDraftedPost(): void
    {
        /** @var Post $post */
        $post = $this->posts->first();
        $updatedPost = Post::factory()->make();
        $newCategories = Category::factory(4)->create()->pluck('id')->toArray();
        $image = UploadedFile::fake()->image('something.jpg');

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm([
                'title' => $updatedPost->title,
                'slug' => $updatedPost->slug,
                'body' => $updatedPost->body,
                'categories' => $newCategories,
            ])
            ->set('data.cover', [$image])
            ->callAction('save-drafted')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Post::class, [
            'title' => $updatedPost->title,
            'slug' => $updatedPost->slug,
            'body' => $updatedPost->body,
            'published_at' => null,
        ]);

        foreach ($newCategories as $newCategory) {
            $this->assertDatabaseHas('post_has_categories', [
                'post_id' => $post->getKey(),
                'category_id' => $newCategory,
            ]);
        }

        $cover = $post->fresh()->getFirstMedia('covers');

        Storage::disk('public')->assertExists("{$cover->getKey()}/{$cover->file_name}");
    }
}
