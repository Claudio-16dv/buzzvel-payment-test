<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->char('currency', 3);

            // Exchange data captured at creation time and kept immutable.
            $table->decimal('exchange_rate', 20, 8);
            $table->decimal('amount_in_eur', 15, 2);
            $table->string('rate_source');
            $table->timestamp('rate_fetched_at');

            $table->string('status')->default('pending')->index();
            $table->text('description')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
