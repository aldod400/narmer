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
        Schema::create('paymob_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('trnx_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('txn_response_code')->nullable();
            $table->text('message')->nullable();
            $table->boolean('pending')->default(false);
            $table->boolean('success')->default(false);
            $table->string('type')->nullable();
            $table->string('source_data_sub_type')->nullable();
            $table->foreignId('my_order_id')->nullable()->references('id')->on('orders')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paymob_payments');
    }
};
