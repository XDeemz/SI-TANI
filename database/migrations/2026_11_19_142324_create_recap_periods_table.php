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
        Schema::create('recap_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commodity_offer_id')->unique()->constrained('commodity_offers')->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained('commodities')->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trader_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('locked_capacity', 10, 2);
            $table->enum('status', ['ready', 'active', 'closed', 'paid'])->default('ready');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recap_periods');
    }
};
