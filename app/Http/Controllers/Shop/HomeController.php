<?php

namespace App\Http\Controllers\Shop;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::withCount([
            'products' => fn ($query) => $query->where('status', ProductStatus::Approved),
        ])->orderBy('name')->get();

        $products = Product::approved()
            ->with('images')
            ->latest('id')
            ->take(12)
            ->get();

        return view('shop.home', [
            'categories' => $categories,
            'products' => $products,
            'slides' => $this->slides(),
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function slides(): array
    {
        return [
            [
                'image' => 'assets/images/slider/banner-1.webp',
                'heading' => 'Natural Snacks for Unstoppable Tail Wags!',
            ],
            [
                'image' => 'assets/images/slider/banner-2.webp',
                'heading' => 'Wholesome Meals for a Happier, Healthier Cat',
            ],
            [
                'image' => 'assets/images/slider/banner-3.webp',
                'heading' => 'Fuel Their Fun with 100% Organic Woof Treats!',
            ],
        ];
    }
}
