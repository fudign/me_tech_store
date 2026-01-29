<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of all orders
     */
    public function index(Request $request)
    {
        $query = Order::with('items.product');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order number, customer name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%");
            });
        }

        $orders = $query->recent()->paginate(20);

        // Get status counts for filter
        $statusCounts = [
            'all' => Order::count(),
            'new' => Order::where('status', Order::STATUS_NEW)->count(),
            'processing' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'delivering' => Order::where('status', Order::STATUS_DELIVERING)->count(),
            'completed' => Order::where('status', Order::STATUS_COMPLETED)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load('items.product');

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:new,processing,delivering,completed'],
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Статус заказа обновлен');
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order)
    {
        // Delete order items first (cascade should handle this, but being explicit)
        $order->items()->delete();

        // Delete order
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('success', 'Заказ успешно удален');
    }
}
