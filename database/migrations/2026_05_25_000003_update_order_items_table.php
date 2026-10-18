<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'pizza_id')) {
                $table->foreignId('pizza_id')->nullable()->constrained('pizzas')->onDelete('cascade')->after('order_id');
            }
        });

        if (Schema::hasColumn('order_items', 'product_id') && Schema::hasColumn('order_items', 'pizza_id')) {
            DB::table('order_items')->whereNull('pizza_id')->update(['pizza_id' => DB::raw('product_id')]);
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('order_items', 'product_name')) {
                $table->dropColumn('product_name');
            }
            if (Schema::hasColumn('order_items', 'total')) {
                $table->dropColumn('total');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('pizzas')->onDelete('cascade')->after('order_id');
            }
            if (! Schema::hasColumn('order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('order_items', 'total')) {
                $table->decimal('total', 10, 2)->nullable()->after('price');
            }
        });

        if (Schema::hasColumn('order_items', 'pizza_id') && Schema::hasColumn('order_items', 'product_id')) {
            DB::table('order_items')->whereNull('product_id')->update(['product_id' => DB::raw('pizza_id')]);
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'pizza_id')) {
                $table->dropForeign(['pizza_id']);
                $table->dropColumn('pizza_id');
            }
        });
    }
};
