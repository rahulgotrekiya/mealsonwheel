<?php

use App\Enums\ProductStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // A category holding products cannot be deleted out from under them.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();

            // The merchant supplying this product. Null means the store stocks
            // it itself rather than sourcing it from a merchant.
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('additional_info')->nullable();
            $table->decimal('price', 10, 2);

            // Units held in the warehouse. Merchants keep this current for
            // their own products; every sale decrements it.
            $table->unsignedInteger('stock')->default(0);

            // Merchant listings are reviewed before they reach the storefront.
            $table->enum('status', array_column(ProductStatus::cases(), 'value'))
                ->default(ProductStatus::Pending->value);
            $table->text('reject_reason')->nullable();

            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            // Order lines reference products, so they are never hard deleted.
            $table->softDeletes();

            $table->index(['status', 'category_id']);
            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
