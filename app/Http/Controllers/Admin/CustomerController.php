<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total');

        // Search by name, email, or phone
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by registration status
        if ($request->has('verified')) {
            if ($request->verified === 'yes') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->verified === 'no') {
                $query->whereNull('email_verified_at');
            }
        }

        // Order by
        $orderBy = $request->input('order_by', 'created_at');
        $orderDir = $request->input('order_dir', 'desc');

        if ($orderBy === 'total_spent') {
            $query->orderBy('total_spent', $orderDir);
        } elseif ($orderBy === 'orders_count') {
            $query->orderBy('orders_count', $orderDir);
        } else {
            $query->orderBy($orderBy, $orderDir);
        }

        $customers = $query->paginate(20);

        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Display the specified customer
     */
    public function show(User $customer)
    {
        $customer->load([
            'orders' => function ($query) {
                $query->with('items.product')->latest();
            }
        ]);

        // Customer statistics
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'total_spent' => $customer->orders()
                ->whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERING])
                ->sum('total_amount'),
            'average_order' => 0,
            'completed_orders' => $customer->orders()
                ->where('status', Order::STATUS_COMPLETED)
                ->count(),
            'pending_orders' => $customer->orders()
                ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_PROCESSING])
                ->count(),
        ];

        if ($stats['total_orders'] > 0) {
            $stats['average_order'] = $stats['total_spent'] / $stats['total_orders'];
        }

        return view('admin.customers.show', compact('customer', 'stats'));
    }
}
