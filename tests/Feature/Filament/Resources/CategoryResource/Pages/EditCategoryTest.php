<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\BaseCategoryResource;

class EditCategoryTest extends BaseCategoryResource
{
    protected Category $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->record = $this->categories->first();
    }

    public function testCanRenderEditPage(): void
    {
        $this->get(CategoryResource::getUrl('edit', [
            'record' => $this->record,
        ]))->assertOk();
    }

    public function testHasAForm(): void
    {
        Livewire::test(EditCategory::class, ['record' => $this->record->getRouteKey()])
            ->assertFormExists();
    }

    public function testHasNameField(): void
    {
        Livewire::test(EditCategory::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('name');
    }

    public function testHasSlugField(): void
    {
        Livewire::test(EditCategory::class, ['record' => $this->record->getRouteKey()])
            ->assertFormFieldExists('slug');
    }

    public function testCanAutomaticallyGenerateASlugFromTheTitle(): void
    {
        $name = fake()->sentence();

        Livewire::test(EditCategory::class, ['record' => $this->record->getRouteKey()])
            ->fillForm([
                'name' => $name,
            ])
            ->assertFormSet([
                'slug' => str($name)->slug(),
            ]);
    }

    public function testCanValidateInput(): void
    {
        $category = $this->categories->sortByDesc('id')->first();

        Livewire::test(EditCategory::class, ['record' => $this->record->getRouteKey()])
            ->fillForm([
                'name' => $category->name,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'name' => ['unique:categories,name'],
                'slug' => ['unique:categories,slug'],
            ]);
    }

    public function testCanRetrieveCategory(): void
    {
        /** @var Category $category */
        $category = $this->categories->first();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->assertFormSet([
                'name' => $category->name,
                'slug' => $category->slug,
            ]);
    }

    public function testCanUpdateCategory(): void
    {
        /** @var Category $category */
        $category = $this->categories->first();
        $updatedCategory = Category::factory()->make();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm([
                'name' => $updatedCategory->name,
                'slug' => $updatedCategory->slug,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Category::class, [
            'name' => $updatedCategory->name,
            'slug' => $updatedCategory->slug,
        ]);
    }
}
