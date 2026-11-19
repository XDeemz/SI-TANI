<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recap_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recap_period_id')->constrained('recap_periods')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->decimal('net_weight', 10, 2);
            $table->decimal('actual_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['recap_period_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recap_items');
    }
};
