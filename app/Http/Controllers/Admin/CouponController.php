<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of coupons
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhere('expires_at', '<', now());
                });
            }
        }

        // Search by code
        if ($search = $request->input('search')) {
            $query->where('code', 'like', "%{$search}%");
        }

        $coupons = $query->latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new coupon
     */
    public function create()
    {
        return view('admin.coupons.create');
    }

    /**
     * Store a newly created coupon
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        // Convert to cents
        if ($data['type'] === Coupon::TYPE_FIXED) {
            $data['value'] = (int) ($data['value'] * 100);
        }

        if (isset($data['min_order_amount'])) {
            $data['min_order_amount'] = (int) ($data['min_order_amount'] * 100);
        }

        if (isset($data['max_discount_amount'])) {
            $data['max_discount_amount'] = (int) ($data['max_discount_amount'] * 100);
        }

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Купон создан успешно');
    }

    /**
     * Show the form for editing the specified coupon
     */
    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    /**
     * Update the specified coupon
     */
    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        // Convert to cents
        if ($data['type'] === Coupon::TYPE_FIXED) {
            $data['value'] = (int) ($data['value'] * 100);
        }

        if (isset($data['min_order_amount'])) {
            $data['min_order_amount'] = (int) ($data['min_order_amount'] * 100);
        }

        if (isset($data['max_discount_amount'])) {
            $data['max_discount_amount'] = (int) ($data['max_discount_amount'] * 100);
        }

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Купон обновлен успешно');
    }

    /**
     * Remove the specified coupon
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Купон удален');
    }
}
