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
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'order_number')) {
                $table->dropColumn('order_number');
            }
            if (Schema::hasColumn('orders', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('orders', 'discount')) {
                $table->dropColumn('discount');
            }
            if (Schema::hasColumn('orders', 'order_status')) {
                $table->dropColumn('order_status');
            }
            if (Schema::hasColumn('orders', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('orders', 'shipping_address')) {
                $table->dropColumn('shipping_address');
            }
            if (Schema::hasColumn('orders', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
            if (Schema::hasColumn('orders', 'customer_email')) {
                $table->dropColumn('customer_email');
            }
            if (Schema::hasColumn('orders', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }
            if (! Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('pending')->after('total');
            }
            if (! Schema::hasColumn('orders', 'address')) {
                $table->text('address')->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->after('id');
            }
            if (! Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->after('user_id');
            }
            if (! Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('subtotal');
            }
            if (! Schema::hasColumn('orders', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('shipping_cost');
            }
            if (! Schema::hasColumn('orders', 'order_status')) {
                $table->string('order_status')->default('pending')->after('payment_status');
            }
            if (! Schema::hasColumn('orders', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('order_status');
            }
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('sslcommerz')->after('transaction_id');
            }
            if (! Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->after('shipping_address');
            }
            if (! Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->after('customer_name');
            }
            if (! Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone')->after('customer_email');
            }
            if (! Schema::hasColumn('orders', 'created_at') && ! Schema::hasColumn('orders', 'updated_at')) {
                $table->timestamps();
            }

            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('orders', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};
