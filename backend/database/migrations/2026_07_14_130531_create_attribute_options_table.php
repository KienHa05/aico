<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attribute_id')
                ->constrained();

            $table->string('value');

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'attribute_id',
                'value',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_options');
    }
};
