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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('fuel_average', 5, 2)->default(8.00)->after('odometer');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('fuel_rate_per_liter', 8, 2)->nullable()->after('end_odometer');
            $table->decimal('toll_tax', 8, 2)->default(0)->after('fuel_rate_per_liter');
            $table->decimal('misc_expense', 8, 2)->default(0)->after('toll_tax');
            $table->decimal('fuel_cost', 10, 2)->default(0)->after('misc_expense');
            $table->decimal('total_cost', 10, 2)->default(0)->after('fuel_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('fuel_average');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'fuel_rate_per_liter',
                'toll_tax',
                'misc_expense',
                'fuel_cost',
                'total_cost'
            ]);
        });
    }
};
