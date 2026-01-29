<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Display checkout form
     */
    public function index()
    {
        // Check cart not empty
        if (Cart::getTotalQuantity() === 0) {
            return redirect()->route('cart.index')
                ->withErrors(['cart' => 'Ваша корзина пуста']);
        }

        $items = Cart::getContent();
        $total = Cart::getTotal();

        return view('checkout.index', compact('items', 'total'));
    }

    /**
     * Process checkout and create order
     */
    public function process(CheckoutRequest $request)
    {
        try {
            $order = DB::transaction(function () use ($request) {
                // 1. Create order
                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'customer_name' => $request->name,
                    'customer_phone' => $request->phone,
                    'customer_address' => $request->address,
                    'payment_method' => $request->payment_method,
                    'status' => 'new',
                    'subtotal' => Cart::getSubTotal() * 100, // Convert to cents
                    'total' => Cart::getTotal() * 100, // Convert to cents
                ]);

                // 2. Create order items (snapshot prices)
                $cartItems = Cart::getContent();
                foreach ($cartItems as $item) {
                    $order->items()->create([
                        'product_id' => $item->id,
                        'product_name' => $item->name,
                        'product_slug' => $item->attributes->slug,
                        'price' => $item->price * 100, // Convert to cents
                        'quantity' => $item->quantity,
                        'subtotal' => ($item->price * $item->quantity) * 100, // Convert to cents
                        'attributes' => $item->attributes->toArray(),
                    ]);
                }

                // 3. Clear cart
                Cart::clear();

                return $order;
            });

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', 'Заказ успешно оформлен!');

        } catch (\Exception $e) {
            // Transaction auto-rolled back
            \Log::error('Checkout error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return back()
                ->withErrors(['checkout' => 'Ошибка при оформлении заказа: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show order confirmation
     */
    public function success($orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        return view('checkout.success', compact('order'));
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        // Format: ORD-YYYYMMDD-NNNN
        $date = now()->format('Ymd');
        $count = Order::whereDate('created_at', now())->count() + 1;
        return sprintf('ORD-%s-%04d', $date, $count);
    }
}
