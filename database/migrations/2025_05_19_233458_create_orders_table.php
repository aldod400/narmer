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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'pending',
                'confirmed',
                'preparing',
                'ready',
                'on_delivery',
                'delivered',
                'canceled',
            ])->default('pending');

            $table->enum('payment_method', [
                'cash',
                'visa',
                'wallet'
            ])->default('cash');
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2)->default(0)->nullable();

            $table->text('notes')->nullable();
            $table->enum('order_type', ['online', 'pos'])->default('online');
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->decimal('discount', 8, 2)->default(0);
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('deliveryman_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
