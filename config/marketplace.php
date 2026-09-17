<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Commission Rate
    |--------------------------------------------------------------------------
    |
    | The percentage the platform retains from each merchant sale. The rate in
    | effect at the moment of a sale is copied onto the order line, so changing
    | this value never alters historical orders or past earnings figures.
    |
    */

    // NOTE: a single global rate. Move to a settings table or a per-merchant
    // column only if rates ever need to differ between merchants.
    'commission_rate' => (float) env('COMMISSION_RATE', 10),

    /*
    |--------------------------------------------------------------------------
    | Cart
    |--------------------------------------------------------------------------
    |
    | Maximum units of a single product one customer may hold in their cart.
    |
    */

    'max_quantity_per_item' => 5,

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    |
    | Stock level at or below which a product is flagged as low on the merchant
    | and admin dashboards.
    |
    */

    'low_stock_threshold' => 5,

];
