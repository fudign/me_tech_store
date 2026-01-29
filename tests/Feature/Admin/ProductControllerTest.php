<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user (adjust based on your auth setup)
        $this->admin = User::factory()->create();
    }

    /** @test */
    public function it_displays_products_index_page(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
        $response->assertViewHas('products');
    }

    /** @test */
    public function it_displays_create_product_form(): void
    {
        Category::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->get(route('admin.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.create');
        $response->assertViewHas('categories');
    }

    /** @test */
    public function it_stores_a_new_product_successfully(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100, // 100 KGS (will be converted to 10000 cents by request)
            'old_price' => 150, // 150 KGS
            'availability_status' => 'in_stock',
            'is_active' => true,
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
            'categories' => [$category->id],
            'images' => [
                UploadedFile::fake()->image('product1.jpg'),
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $productData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Товар успешно создан');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 10000,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_storing_product(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), []);

        $response->assertSessionHasErrors(['name', 'price', 'availability_status']);
    }

    /** @test */
    public function it_displays_edit_product_form(): void
    {
        $product = Product::factory()->create();
        Category::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.edit');
        $response->assertViewHas('product', $product);
    }

    /** @test */
    public function it_updates_an_existing_product(): void
    {
        $product = Product::factory()->create(['name' => 'Old Name']);
        $category = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated Description',
            'price' => 200, // 200 KGS (will be converted to 20000 cents)
            'availability_status' => 'in_stock',
            'is_active' => true,
            'categories' => [$category->id],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $updateData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Товар успешно обновлен');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 20000,
        ]);
    }

    /** @test */
    public function it_deletes_a_product(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create([
            'images' => ['products/test.jpg'],
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Товар успешно удален');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_attaches_categories_to_product(): void
    {
        $product = Product::factory()->create();
        $categories = Category::factory()->count(2)->create();

        $updateData = [
            'name' => $product->name,
            'price' => $product->price,
            'availability_status' => $product->availability_status,
            'is_active' => $product->is_active,
            'categories' => $categories->pluck('id')->toArray(),
        ];

        $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $updateData);

        $this->assertEquals(2, $product->fresh()->categories()->count());
    }

    /** @test */
    public function guests_cannot_access_admin_product_pages(): void
    {
        $product = Product::factory()->create();

        $this->get(route('admin.products.index'))->assertRedirect();
        $this->get(route('admin.products.create'))->assertRedirect();
        $this->post(route('admin.products.store'), [])->assertRedirect();
        $this->get(route('admin.products.edit', $product))->assertRedirect();
        $this->put(route('admin.products.update', $product), [])->assertRedirect();
        $this->delete(route('admin.products.destroy', $product))->assertRedirect();
    }
}
