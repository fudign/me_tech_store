<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
    }

    /** @test */
    public function it_displays_categories_index_page(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
        $response->assertViewHas('categories');
    }

    /** @test */
    public function it_displays_create_category_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.create');
    }

    /** @test */
    public function it_stores_a_new_category_successfully(): void
    {
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'is_active' => true,
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Категория успешно создана');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);
    }

    /** @test */
    public function it_validates_required_name_field(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), []);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_displays_edit_category_form(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.edit');
        $response->assertViewHas('category', $category);
    }

    /** @test */
    public function it_updates_an_existing_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Category']);

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description',
            'is_active' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), $updateData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Категория успешно обновлена');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    /** @test */
    public function it_deletes_a_category_without_products(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Категория удалена');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_prevents_deleting_category_with_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $category->products()->attach($product);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Невозможно удалить категорию с товарами');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_generates_slug_automatically(): void
    {
        $categoryData = [
            'name' => 'Test Category Name',
            'is_active' => true,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category Name',
            'slug' => 'test-category-name',
        ]);
    }

    /** @test */
    public function guests_cannot_access_admin_category_pages(): void
    {
        $category = Category::factory()->create();

        $this->get(route('admin.categories.index'))->assertRedirect();
        $this->get(route('admin.categories.create'))->assertRedirect();
        $this->post(route('admin.categories.store'), [])->assertRedirect();
        $this->get(route('admin.categories.edit', $category))->assertRedirect();
        $this->put(route('admin.categories.update', $category), [])->assertRedirect();
        $this->delete(route('admin.categories.destroy', $category))->assertRedirect();
    }
}
