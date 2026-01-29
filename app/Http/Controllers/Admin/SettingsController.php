<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings form
     */
    public function index()
    {
        $settings = [
            'phone' => Setting::get('site.phone', ''),
            'address' => Setting::get('site.address', ''),
            'email' => Setting::get('site.email', ''),
            'footer_text' => Setting::get('site.footer_text', ''),
            'whatsapp' => Setting::get('site.whatsapp', ''),

            // Map coordinates
            'map_latitude' => Setting::get('site.map_latitude', '42.8746'),
            'map_longitude' => Setting::get('site.map_longitude', '74.5698'),

            // Hero banner settings
            'hero_badge' => Setting::get('hero.badge', 'Новинка'),
            'hero_title' => Setting::get('hero.title', 'Xiaomi 14 Ultra'),
            'hero_subtitle' => Setting::get('hero.subtitle', 'Оптика Leica.'),
            'hero_description' => Setting::get('hero.description', 'Легендарная оптика, процессор Snapdragon 8 Gen 3 и новый иммерсивный дисплей.'),
            'hero_image_url' => Setting::get('hero.image_url', 'https://hoirqrkdgbmvpwutwuwj.supabase.co/storage/v1/object/public/assets/assets/917d6f93-fb36-439a-8c48-884b67b35381_1600w.jpg'),
            'hero_product_id' => Setting::get('hero.product_id', ''),
        ];

        // Get all products for dropdown
        $products = \App\Models\Product::active()
            ->orderBy('name')
            ->get();

        return view('admin.settings.index', compact('settings', 'products'));
    }

    /**
     * Update the settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:100',
            'footer_text' => 'nullable|string|max:500',
            'whatsapp' => 'nullable|string|max:20',

            // Map coordinates validation
            'map_coordinates' => 'nullable|string|max:50',

            // Hero banner validation
            'hero_badge' => 'nullable|string|max:50',
            'hero_title' => 'nullable|string|max:200',
            'hero_subtitle' => 'nullable|string|max:200',
            'hero_description' => 'nullable|string|max:500',
            'hero_image_url' => 'nullable|url|max:500',
            'hero_product_id' => 'nullable|exists:products,id',
        ]);

        // Site settings
        Setting::set('site.phone', $request->phone);
        Setting::set('site.address', $request->address);
        Setting::set('site.email', $request->email);
        Setting::set('site.footer_text', $request->footer_text ?? '');
        Setting::set('site.whatsapp', $request->whatsapp ?? '');

        // Parse and save map coordinates
        if ($request->filled('map_coordinates')) {
            // Remove extra spaces and parse coordinates
            $coordinates = preg_replace('/\s+/', '', $request->map_coordinates);
            $parts = explode(',', $coordinates);

            if (count($parts) === 2) {
                $lat = trim($parts[0]);
                $lng = trim($parts[1]);

                // Validate coordinate ranges
                if (is_numeric($lat) && is_numeric($lng) &&
                    $lat >= -90 && $lat <= 90 &&
                    $lng >= -180 && $lng <= 180) {
                    Setting::set('site.map_latitude', $lat);
                    Setting::set('site.map_longitude', $lng);
                }
            }
        } else {
            // Clear coordinates if field is empty
            Setting::set('site.map_latitude', null);
            Setting::set('site.map_longitude', null);
        }

        // Hero banner settings
        Setting::set('hero.badge', $request->hero_badge);
        Setting::set('hero.title', $request->hero_title);
        Setting::set('hero.subtitle', $request->hero_subtitle);
        Setting::set('hero.description', $request->hero_description);
        Setting::set('hero.image_url', $request->hero_image_url);
        Setting::set('hero.product_id', $request->hero_product_id);

        return back()->with('success', 'Настройки сохранены');
    }
}
