<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Models\Category;
use Livewire\Livewire;
use Tests\Feature\Filament\Resources\BaseCategoryResource;

class CreateCategoryTest extends BaseCategoryResource
{
    public function testCanRenderCreatePage(): void
    {
        $this->get(CategoryResource::getUrl('create'))->assertOk();
    }

    public function testHasAForm(): void
    {
        Livewire::test(CreateCategory::class)
            ->assertFormExists();
    }

    public function testHasNameField(): void
    {
        Livewire::test(CreateCategory::class)
            ->assertFormFieldExists('name');
    }

    public function testHasSlugField(): void
    {
        Livewire::test(CreateCategory::class)
            ->assertFormFieldExists('slug');
    }

    public function testCanAutomaticallyGenerateASlugFromTheName(): void
    {
        $name = fake()->sentence();

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => $name,
            ])
            ->assertFormSet([
                'slug' => str($name)->slug(),
            ]);
    }

    public function testCanValidateInput(): void
    {
        $category = $this->categories->first();

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => $category->name,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => ['unique:categories,name'],
                'slug' => ['unique:categories,slug'],
            ]);
    }

    public function testCanCreateCategory(): void
    {
        $category = Category::factory()->makeOne();
        $name = $category->name;
        $slug = str($name)->slug();

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => $name,
                'slug' => $slug,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Category::class, [
            'name' => $name,
            'slug' => $slug,
        ]);
    }
}
