<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Sort options, mapped to the column and direction each one means.
     *
     * Keeping this as a lookup rather than interpolating the request value means
     * an unrecognised choice falls back to the default instead of reaching SQL.
     */
    private const SORTS = [
        'price_asc' => ['price', 'asc'],
        'price_desc' => ['price', 'desc'],
        'name_asc' => ['name', 'asc'],
        'name_desc' => ['name', 'desc'],
    ];

    public function __invoke(Request $request): View
    {
        $keyword = trim((string) $request->query('keyword', ''));

        $matches = Product::approved()
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', '%'.$keyword.'%'));

        // The slider bounds come from what actually matched, so the control
        // never offers a range that returns nothing.
        $bounds = (clone $matches)->selectRaw('MIN(price) AS min_price, MAX(price) AS max_price')->first();

        $floor = (float) ($bounds->min_price ?? 0);
        $ceiling = (float) ($bounds->max_price ?? 0);

        $minPrice = $request->filled('min_price') ? (float) $request->query('min_price') : $floor;
        $maxPrice = $request->filled('max_price') ? (float) $request->query('max_price') : $ceiling;

        if ($minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        $sort = $request->query('sort', 'default');
        [$column, $direction] = self::SORTS[$sort] ?? ['name', 'asc'];

        $products = $matches
            ->whereBetween('price', [$minPrice, $maxPrice])
            ->with('images')
            ->orderBy($column, $direction)
            ->paginate(24)
            ->withQueryString();

        return view('shop.search', [
            'keyword' => $keyword,
            'products' => $products,
            'sort' => $sort,
            'sortOptions' => self::SORTS,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'floor' => floor($floor),
            'ceiling' => ceil($ceiling),
        ]);
    }
}
