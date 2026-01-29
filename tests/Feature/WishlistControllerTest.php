<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_wishlist_page(): void
    {
        $response = $this->get(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertViewIs('wishlist.index');
        $response->assertViewHas('products');
    }

    /** @test */
    public function it_displays_only_active_products_in_wishlist(): void
    {
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);

        // Add both to session wishlist
        session(['wishlist' => [$activeProduct->id, $inactiveProduct->id]]);

        $response = $this->get(route('wishlist.index'));

        $response->assertStatus(200);
        $products = $response->viewData('products');

        $this->assertCount(1, $products);
        $this->assertEquals($activeProduct->id, $products->first()->id);
    }

    /** @test */
    public function it_adds_product_to_wishlist(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->postJson(route('wishlist.toggle'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'inWishlist' => true,
            'count' => 1,
            'message' => 'Добавлено в избранное',
        ]);

        $this->assertTrue(in_array($product->id, session('wishlist', [])));
    }

    /** @test */
    public function it_removes_product_from_wishlist_when_toggling(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        // Add to wishlist first
        session(['wishlist' => [$product->id]]);

        $response = $this->postJson(route('wishlist.toggle'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'inWishlist' => false,
            'count' => 0,
            'message' => 'Удалено из избранного',
        ]);

        $this->assertFalse(in_array($product->id, session('wishlist', [])));
    }

    /** @test */
    public function it_validates_product_id_when_toggling(): void
    {
        $response = $this->postJson(route('wishlist.toggle'), [
            'product_id' => 9999, // Non-existent product
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function it_prevents_adding_inactive_products_to_wishlist(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $response = $this->postJson(route('wishlist.toggle'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_wishlist_count(): void
    {
        $products = Product::factory()->count(3)->create(['is_active' => true]);
        session(['wishlist' => $products->pluck('id')->toArray()]);

        $response = $this->getJson(route('wishlist.count'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 3,
        ]);
    }

    /** @test */
    public function it_returns_zero_when_wishlist_is_empty(): void
    {
        $response = $this->getJson(route('wishlist.count'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 0,
        ]);
    }

    /** @test */
    public function it_maintains_wishlist_across_requests(): void
    {
        $product1 = Product::factory()->create(['is_active' => true]);
        $product2 = Product::factory()->create(['is_active' => true]);

        // Add first product
        $this->postJson(route('wishlist.toggle'), ['product_id' => $product1->id]);

        // Add second product
        $this->postJson(route('wishlist.toggle'), ['product_id' => $product2->id]);

        $wishlist = session('wishlist', []);
        $this->assertCount(2, $wishlist);
        $this->assertTrue(in_array($product1->id, $wishlist));
        $this->assertTrue(in_array($product2->id, $wishlist));
    }
}
