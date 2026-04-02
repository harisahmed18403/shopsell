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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->unique()->after('id');
            $table->decimal('amount_paid', 10, 2)->nullable()->after('total_amount');
            $table->string('payment_method')->nullable()->after('amount_paid');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('product_id');
            $table->string('model')->nullable()->after('brand');
            $table->string('storage')->nullable()->after('model');
            $table->string('color')->nullable()->after('storage');
            $table->string('imei_1')->nullable()->after('color');
            $table->string('imei_2')->nullable()->after('imei_1');
            $table->string('condition_grade')->nullable()->after('imei_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn([
                'brand',
                'model',
                'storage',
                'color',
                'imei_1',
                'imei_2',
                'condition_grade',
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['receipt_number']);
            $table->dropColumn([
                'receipt_number',
                'amount_paid',
                'payment_method',
            ]);
        });
    }
};
