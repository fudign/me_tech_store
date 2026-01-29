<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Cart;

class CartController extends Controller
{
    /**
     * Display cart page
     */
    public function index()
    {
        $items = Cart::getContent();
        $total = Cart::getTotal();

        return view('cart.index', compact('items', 'total'));
    }

    /**
     * Add to cart (AJAX, stay on current page)
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        try {
            $product = Product::findOrFail($request->product_id);

            Cart::add([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price / 100, // Convert cents to decimal for cart
                'quantity' => $request->quantity ?? 1,
                'attributes' => [
                    'slug' => $product->slug,
                    'image' => $product->main_image,
                ]
            ]);

            return response()->json([
                'success' => true,
                'cart_count' => Cart::getTotalQuantity(),
                'message' => 'Товар добавлен в корзину'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при добавлении товара в корзину'
            ], 500);
        }
    }

    /**
     * Update quantity (AJAX, no page reload)
     */
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            Cart::update($itemId, [
                'quantity' => [
                    'relative' => false,
                    'value' => $request->quantity
                ]
            ]);

            return response()->json([
                'success' => true,
                'subtotal' => Cart::getSubTotal(),
                'total' => Cart::getTotal(),
                'cart_count' => Cart::getTotalQuantity()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении количества'
            ], 500);
        }
    }

    /**
     * Remove item (AJAX)
     */
    public function remove($itemId)
    {
        try {
            Cart::remove($itemId);

            return response()->json([
                'success' => true,
                'total' => Cart::getTotal(),
                'cart_count' => Cart::getTotalQuantity(),
                'message' => 'Товар удален из корзины'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении товара'
            ], 500);
        }
    }
}
