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
        Schema::create('super_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Non-incrementing CeX ID
            $table->string('name');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('product_lines', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Non-incrementing CeX ID
            $table->string('name');
            $table->foreignId('super_category_id')->constrained('super_categories')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Non-incrementing CeX ID
            $table->string('name');
            $table->foreignId('product_line_id')->constrained('product_lines')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('product_lines');
        Schema::dropIfExists('super_categories');
    }
};
