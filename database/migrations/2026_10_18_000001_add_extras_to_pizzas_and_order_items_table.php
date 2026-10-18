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
        Schema::table('pizzas', function (Blueprint $table) {
            if (! Schema::hasColumn('pizzas', 'extras')) {
                $table->json('extras')->nullable()->after('size');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'extras')) {
                $table->json('extras')->nullable()->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pizzas', function (Blueprint $table) {
            if (Schema::hasColumn('pizzas', 'extras')) {
                $table->dropColumn('extras');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'extras')) {
                $table->dropColumn('extras');
            }
        });
    }
};
