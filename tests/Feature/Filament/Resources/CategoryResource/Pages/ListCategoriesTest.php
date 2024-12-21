<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use Filament\Tables\Actions\DeleteAction;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\BaseCategoryResource;

class ListCategoriesTest extends BaseCategoryResource
{
    public function testCanRenderIndexPage(): void
    {
        $this->get(CategoryResource::getUrl('index'))->assertOk();
    }

    public function testCanListPosts(): void
    {
        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords($this->categories)
            ->assertCountTableRecords(10);
    }

    public function testCanRenderNameColumn(): void
    {
        Livewire::test(ListCategories::class)->assertCanRenderTableColumn('name');
    }

    public function testCanGetCategoryName(): void
    {
        $category = $this->categories->first();

        Livewire::test(ListCategories::class)
            ->assertTableColumnStateSet('name', $category->name, $category);
    }

    public function testCanSortByName(): void
    {
        Livewire::test(ListCategories::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($this->categories->sortBy('name'), true)
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords($this->categories->sortByDesc('name'), true);
    }

    public function testCanSearchByName(): void
    {
        $name = $this->categories->first()->name;

        Livewire::test(ListCategories::class)
            ->searchTable($name)
            ->assertCanSeeTableRecords($this->categories->where('name', $name))
            ->assertCanNotSeeTableRecords($this->categories->where('name', '!=', $name));
    }

    public function testCanDeleteCategory(): void
    {
        $deletedCategory = $this->categories->first();

        Livewire::test(ListCategories::class)
            ->callTableAction(DeleteAction::class, $deletedCategory);

        $this->assertModelMissing($deletedCategory);
    }
}
