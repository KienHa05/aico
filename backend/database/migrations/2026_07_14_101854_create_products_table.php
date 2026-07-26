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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained();

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('brand')->nullable();

            $table->string('short_description')->nullable();

            $table->longText('description')->nullable();

            $table->string('status')->default('draft');

            $table->boolean('is_featured')->default(false);

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
