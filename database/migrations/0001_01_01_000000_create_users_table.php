<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname', 50);
            $table->string('lastname', 50);
            $table->string('email')->unique();
            $table->string('password');

            // Every account lives in this one table; the role decides which
            // panel it can reach. Merchants additionally need an active status,
            // since they sign themselves up and wait for approval.
            $table->enum('role', array_column(UserRole::cases(), 'value'))
                ->default(UserRole::Customer->value);
            $table->enum('status', array_column(UserStatus::cases(), 'value'))
                ->default(UserStatus::Active->value);

            $table->string('phone', 20)->nullable();
            $table->string('photo')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Orders reference their customer, so accounts are never truly
            // removed; deleting one would take its order history with it.
            $table->softDeletes();

            $table->index(['role', 'status']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
