<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Cart;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cart before each test
        Cart::clear();
    }

    /** @test */
    public function it_displays_cart_page(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
        $response->assertViewHas(['items', 'total']);
    }

    /** @test */
    public function it_adds_product_to_cart_successfully(): void
    {
        $product = Product::factory()->create([
            'price' => 10000, // 100.00 in cents
            'is_active' => true,
        ]);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Товар добавлен в корзину',
        ]);

        $this->assertEquals(2, Cart::getTotalQuantity());
    }

    /** @test */
    public function it_validates_product_id_when_adding_to_cart(): void
    {
        $response = $this->postJson(route('cart.add'), [
            'product_id' => 9999, // Non-existent product
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function it_defaults_to_quantity_one_when_not_specified(): void
    {
        $product = Product::factory()->create([
            'price' => 10000,
            'is_active' => true,
        ]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
        ]);

        $this->assertEquals(1, Cart::getTotalQuantity());
    }

    /** @test */
    public function it_updates_cart_item_quantity(): void
    {
        $product = Product::factory()->create([
            'price' => 10000,
            'is_active' => true,
        ]);

        // Add product to cart
        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100,
            'quantity' => 1,
            'attributes' => [
                'slug' => $product->slug,
                'image' => $product->main_image,
            ],
        ]);

        $cartItem = Cart::getContent()->first();

        $response = $this->patchJson(route('cart.update', $cartItem->id), [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertEquals(5, Cart::getTotalQuantity());
    }

    /** @test */
    public function it_validates_quantity_when_updating_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 10000,
            'is_active' => true,
        ]);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100,
            'quantity' => 1,
            'attributes' => [],
        ]);

        $cartItem = Cart::getContent()->first();

        $response = $this->patchJson(route('cart.update', $cartItem->id), [
            'quantity' => 0, // Invalid quantity
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_removes_item_from_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 10000,
            'is_active' => true,
        ]);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100,
            'quantity' => 1,
            'attributes' => [],
        ]);

        $cartItem = Cart::getContent()->first();
        $this->assertEquals(1, Cart::getTotalQuantity());

        $response = $this->deleteJson(route('cart.remove', $cartItem->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Товар удален из корзины',
        ]);

        $this->assertEquals(0, Cart::getTotalQuantity());
    }

    /** @test */
    public function it_returns_cart_count_in_response(): void
    {
        $product = Product::factory()->create([
            'price' => 10000,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertJson([
            'cart_count' => 3,
        ]);
    }

    /** @test */
    public function it_converts_price_to_decimal_when_adding_to_cart(): void
    {
        $product = Product::factory()->create([
            'price' => 15000, // 150.00 in cents
            'is_active' => true,
        ]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartItem = Cart::getContent()->first();
        $this->assertEquals(150.00, $cartItem->price);
    }
}
