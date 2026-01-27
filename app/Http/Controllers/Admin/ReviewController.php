<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews
     */
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user']);

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'pending') {
                $query->pending();
            } elseif ($request->status === 'approved') {
                $query->approved();
            }
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        // Search by product name or customer
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $reviews = $query->latest()->paginate(20);

        // Get counts for stats
        $stats = [
            'total' => Review::count(),
            'pending' => Review::pending()->count(),
            'approved' => Review::approved()->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Display the specified review
     */
    public function show(Review $review)
    {
        $review->load(['product', 'user']);

        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Update review (approve/reject, add response)
     */
    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'is_approved' => 'sometimes|boolean',
            'admin_response' => 'nullable|string|max:1000',
        ]);

        if (isset($data['admin_response'])) {
            $data['admin_response_at'] = now();
        }

        $review->update($data);

        $message = isset($data['is_approved'])
            ? ($data['is_approved'] ? 'Отзыв одобрен' : 'Отзыв отклонен')
            : 'Ответ сохранен';

        return back()->with('success', $message);
    }

    /**
     * Remove the specified review
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Отзыв удален');
    }
}
