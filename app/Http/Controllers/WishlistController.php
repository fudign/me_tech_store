<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class WishlistController extends Controller
{
    /**
     * Display wishlist page
     */
    public function index()
    {
        $wishlistIds = session('wishlist', []);

        // Load products from wishlist IDs
        $products = Product::whereIn('id', $wishlistIds)
            ->active()
            ->with('categories')
            ->get();

        return view('wishlist.index', compact('products'));
    }

    /**
     * Toggle product in wishlist (AJAX)
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $productId = $request->product_id;

            // Check product is active
            $product = Product::where('id', $productId)
                ->active()
                ->firstOrFail();

            $wishlist = session('wishlist', []);

            // Toggle: add if not in wishlist, remove if already in
            if (in_array($productId, $wishlist)) {
                $wishlist = array_values(array_diff($wishlist, [$productId]));
                $inWishlist = false;
                $message = 'Удалено из избранного';
            } else {
                $wishlist[] = $productId;
                $inWishlist = true;
                $message = 'Добавлено в избранное';
            }

            session()->put('wishlist', $wishlist);

            return response()->json([
                'success' => true,
                'inWishlist' => $inWishlist,
                'count' => count($wishlist),
                'message' => $message
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден или неактивен'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении избранного'
            ], 500);
        }
    }

    /**
     * Get current wishlist count (AJAX)
     */
    public function count()
    {
        $count = count(session('wishlist', []));

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
}
