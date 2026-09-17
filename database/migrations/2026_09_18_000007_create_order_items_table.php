<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();

            $table->unsignedSmallInteger('quantity');

            /*
             * The three columns below are copied from the product and the
             * platform settings at the moment the order is placed, and are never
             * updated afterwards.
             *
             * Reading them live instead would mean an edit to a product's price,
             * a change of supplier, or a new commission rate silently rewriting
             * every order already placed, along with the earnings those orders
             * paid out. Snapshotting keeps history fixed: this line records what
             * was actually sold, by whom, on what terms.
             */
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('commission_rate', 5, 2);

            $table->timestamps();

            $table->index(['seller_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
