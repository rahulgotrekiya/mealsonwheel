<?php

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('transaction_id', 50)->unique();

            // What the customer paid for the whole basket. A basket may draw on
            // several merchants, so this figure belongs to the platform and is
            // never shown to a merchant as their own.
            $table->decimal('total_amount', 10, 2);

            // The platform fulfils every order from its own warehouse, so an
            // order carries a single status that only an admin advances.
            $table->enum('status', array_column(OrderStatus::cases(), 'value'))
                ->default(OrderStatus::Confirmed->value);

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
