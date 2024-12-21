<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Models\Post;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\BasePostResource;

class ListPostsTest extends BasePostResource
{
    public function testCanRenderIndexPage(): void
    {
        $this->get(PostResource::getUrl('index'))->assertOk();
    }

    public function testCanListPosts(): void
    {
        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords($this->posts)
            ->assertCountTableRecords(10);
    }

    public function testCanRenderCoverColumn(): void
    {
        Livewire::test(ListPosts::class)->assertCanRenderTableColumn('cover');
    }

    public function testCanRenderTitleColumn(): void
    {
        Livewire::test(ListPosts::class)->assertCanRenderTableColumn('title');
    }

    public function testCanRenderAuthorNameColumn(): void
    {
        Livewire::test(ListPosts::class)->assertCanRenderTableColumn('author.name');
    }

    public function testCanRenderPublishedAtColumn(): void
    {
        Livewire::test(ListPosts::class)->assertCanRenderTableColumn('published_at');
    }

    public function testCanGetPostCover(): void
    {
        /** @var Post $post */
        $post = $this->posts->first();

        Livewire::test(ListPosts::class)
            ->assertTableColumnStateSet('cover', [$post->getFirstMedia('covers')->uuid], $post);
    }

    public function testCanGetPostTitle(): void
    {
        $post = $this->posts->first();

        Livewire::test(ListPosts::class)
            ->assertTableColumnStateSet('title', $post->title, $post);
    }

    public function testCanGetPostPublishedAt(): void
    {
        $post = $this->posts->first();

        Livewire::test(ListPosts::class)
            ->assertTableColumnStateSet('published_at', $post->published_at, $post);
    }

    public function testCanGetPostAuthorNames(): void
    {
        $post = $this->posts->first();

        Livewire::test(ListPosts::class)
            ->assertTableColumnStateSet('author.name', $post->author->name, $post);
    }

    public function testCanSortByTitle(): void
    {
        Livewire::test(ListPosts::class)
            ->sortTable('title')
            ->assertCanSeeTableRecords($this->posts->sortBy('title'), true)
            ->sortTable('title', 'desc')
            ->assertCanSeeTableRecords($this->posts->sortByDesc('title'), true);
    }

    public function testCanSearchByTitle(): void
    {
        $title = $this->posts->first()->title;

        Livewire::test(ListPosts::class)
            ->searchTable($title)
            ->assertCanSeeTableRecords($this->posts->where('title', $title))
            ->assertCanNotSeeTableRecords($this->posts->where('title', '!=', $title));
    }

    public function testCanSoftDeletePost(): void
    {
        $post = $this->posts->first();

        Livewire::test(ListPosts::class)
            ->callTableAction(DeleteAction::class, $post);

        $this->assertSoftDeleted($post);
    }

    public function testCanRestoreSoftDeletedPost(): void
    {
        $this->posts->first()->delete();

        $softDeletedPost = $this->posts->whereNotNull('deleted_at')->first();

        Livewire::test(ListPosts::class)
            ->filterTable(TrashedFilter::class, false)
            ->assertCanSeeTableRecords($this->posts->whereNotNull('deleted_at'))
            ->callTableAction(RestoreAction::class, $softDeletedPost);

        $this->assertNotSoftDeleted($softDeletedPost);
    }

    public function testCanForceDeletePost(): void
    {
        $this->posts->first()->delete();

        /** @var Post $softDeletedPost */
        $softDeletedPost = $this->posts->whereNotNull('deleted_at')->first();
        $softDeletedPostCover = $softDeletedPost->getFirstMedia('covers');

        Livewire::test(ListPosts::class)
            ->filterTable(TrashedFilter::class, false)
            ->assertCanSeeTableRecords($this->posts->whereNotNull('deleted_at'))
            ->callTableAction(ForceDeleteAction::class, $softDeletedPost);

        $this->assertModelMissing($softDeletedPost);

        Storage::disk('public')->assertMissing("{$softDeletedPostCover->getKey()}/{$softDeletedPostCover->file_name}");
    }
}
