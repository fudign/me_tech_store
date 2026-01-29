<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Cart;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear cart before each test
        Cart::clear();
    }

    /** @test */
    public function it_displays_checkout_page_when_cart_has_items(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'is_active' => true]);

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

        $response = $this->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.index');
        $response->assertViewHas(['items', 'total']);
    }

    /** @test */
    public function it_redirects_to_cart_when_checkout_with_empty_cart(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHasErrors(['cart' => 'Ваша корзина пуста']);
    }

    /** @test */
    public function it_processes_checkout_and_creates_order(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'is_active' => true]);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100,
            'quantity' => 2,
            'attributes' => [
                'slug' => $product->slug,
                'image' => $product->main_image,
            ],
        ]);

        $checkoutData = [
            'name' => 'Test Customer',
            'phone' => '+996700123456',
            'address' => 'Test Address',
            'payment_method' => 'cash', // Valid: cash, online, installment
        ];

        $response = $this->post(route('checkout.process'), $checkoutData);

        $order = Order::first();

        $response->assertRedirect(route('checkout.success', $order->order_number));
        $response->assertSessionHas('success', 'Заказ успешно оформлен!');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Test Customer',
            'customer_phone' => '+996700123456',
            'status' => 'new',
        ]);
    }

    /** @test */
    public function it_creates_order_items_with_correct_data(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 15000, // 150.00
            'is_active' => true,
        ]);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100,
            'quantity' => 3,
            'attributes' => [
                'slug' => $product->slug,
                'image' => $product->main_image,
            ],
        ]);

        $checkoutData = [
            'name' => 'Customer',
            'phone' => '+996700123456',
            'address' => 'Address',
            'payment_method' => 'online',
        ];

        $this->post(route('checkout.process'), $checkoutData);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name' => 'Test Product',
            'price' => 15000, // Stored in cents
            'quantity' => 3,
            'subtotal' => 45000, // 150.00 * 3 in cents
        ]);
    }

    /** @test */
    public function it_clears_cart_after_successful_checkout(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'is_active' => true]);

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

        $this->assertEquals(1, Cart::getTotalQuantity());

        $checkoutData = [
            'name' => 'Customer',
            'phone' => '+996700123456',
            'address' => 'Address',
            'payment_method' => 'cash',
        ];

        $this->post(route('checkout.process'), $checkoutData);

        $this->assertEquals(0, Cart::getTotalQuantity());
    }

    /** @test */
    public function it_generates_unique_order_number(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'is_active' => true]);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100,
            'quantity' => 1,
            'attributes' => ['slug' => $product->slug, 'image' => $product->main_image],
        ]);

        $checkoutData = [
            'name' => 'Customer',
            'phone' => '+996700123456',
            'address' => 'Address',
            'payment_method' => 'cash',
        ];

        $this->post(route('checkout.process'), $checkoutData);

        $order = Order::first();

        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{4}$/', $order->order_number);
    }

    /** @test */
    public function it_validates_required_checkout_fields(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'is_active' => true]);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100,
            'quantity' => 1,
            'attributes' => [],
        ]);

        $response = $this->post(route('checkout.process'), []);

        $response->assertSessionHasErrors(['name', 'phone', 'address', 'payment_method']);
    }

    /** @test */
    public function it_displays_order_success_page(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('checkout.success', $order->order_number));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.success');
        $response->assertViewHas('order', $order);
    }

    /** @test */
    public function it_converts_prices_to_cents_when_saving_order(): void
    {
        $product = Product::factory()->create(['price' => 25000, 'is_active' => true]);

        Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price / 100, // 250.00
            'quantity' => 2,
            'attributes' => ['slug' => $product->slug, 'image' => $product->main_image],
        ]);

        $checkoutData = [
            'name' => 'Customer',
            'phone' => '+996700123456',
            'address' => 'Address',
            'payment_method' => 'cash',
        ];

        $this->post(route('checkout.process'), $checkoutData);

        $order = Order::first();

        // 250.00 * 2 = 500.00, stored as 50000 cents
        $this->assertEquals(50000, $order->total);
    }
}
