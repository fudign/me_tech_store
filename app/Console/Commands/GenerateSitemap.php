<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap.xml from active products and categories';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        // Add homepage
        $sitemap->add(
            Url::create('/')
                ->setLastModificationDate(now())
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        // Add categories
        $categories = Category::where('is_active', true)->get();
        foreach ($categories as $category) {
            $sitemap->add(
                Url::create(route('category.show', $category, false))
                    ->setLastModificationDate($category->updated_at)
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }
        $this->info("Added {$categories->count()} categories");

        // Add products
        $products = Product::where('is_active', true)->get();
        foreach ($products as $product) {
            $sitemap->add(
                Url::create(route('product.show', $product, false))
                    ->setLastModificationDate($product->updated_at)
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        }
        $this->info("Added {$products->count()} products");

        // Write sitemap to file
        $sitemap->writeToFile(public_path('sitemap.xml'));

        // Cache generation timestamp
        Cache::put('sitemap_generated_at', now(), now()->addDay());

        $this->info('Sitemap generated successfully at: ' . public_path('sitemap.xml'));

        return Command::SUCCESS;
    }
}
