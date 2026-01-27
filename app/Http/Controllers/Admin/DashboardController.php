<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Date ranges
        $today = Carbon::today();
        $last7Days = Carbon::today()->subDays(7);
        $last30Days = Carbon::today()->subDays(30);
        $thisMonth = Carbon::now()->startOfMonth();

        // Main statistics
        $stats = [
            'total_revenue' => Order::whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERING])
                ->sum('total'),
            'total_orders' => Order::count(),
            'total_customers' => User::whereNotNull('email_verified_at')->count(),
            'total_products' => Product::where('is_active', true)->count(),

            // Monthly stats
            'monthly_revenue' => Order::whereIn('status', [Order::STATUS_COMPLETED, Order::STATUS_DELIVERING])
                ->where('created_at', '>=', $thisMonth)
                ->sum('total'),
            'monthly_orders' => Order::where('created_at', '>=', $thisMonth)->count(),

            // New orders count
            'new_orders' => Order::where('status', Order::STATUS_NEW)->count(),
        ];

        // Average order value
        $stats['average_order'] = $stats['total_orders'] > 0
            ? $stats['total_revenue'] / $stats['total_orders']
            : 0;

        // Orders by status
        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Last 7 days orders chart data
        $last7DaysOrders = Order::where('created_at', '>=', $last7Days)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'), DB::raw('sum(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates with zeros
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $dayData = $last7DaysOrders->firstWhere('date', $date);

            $chartData[] = [
                'date' => Carbon::parse($date)->format('d.m'),
                'orders' => $dayData ? $dayData->count : 0,
                'revenue' => $dayData ? $dayData->revenue : 0,
            ];
        }

        // Top 5 selling products
        $topProducts = OrderItem::select('product_id', DB::raw('sum(quantity) as total_sold'), DB::raw('sum(price * quantity) as total_revenue'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Recent orders
        $recentOrders = Order::with('items.product')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Low stock products (less than 10)
        $lowStockProducts = Product::where('is_active', true)
            ->where('stock_quantity', '<', 10)
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'ordersByStatus',
            'chartData',
            'topProducts',
            'recentOrders',
            'lowStockProducts'
        ));
    }
}
