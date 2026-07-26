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

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('order_number')
                ->unique();

            $table->string('status');

            $table->string('payment_method');
            $table->string('payment_status');

            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)
                ->default(0);
            $table->decimal('shipping_fee', 10, 2)
                ->default(0);
            $table->decimal('tax', 10, 2)
                ->default(0);
            $table->decimal('total', 10, 2);

            $table->string('shipping_full_name');
            $table->string('shipping_phone', 20);

            $table->string('shipping_country');
            $table->string('shipping_state');
            $table->string('shipping_city');
            $table->string('shipping_district');
            $table->string('shipping_ward');
            $table->string('shipping_address_line');
            $table->string('shipping_postal_code')
                ->nullable();

            $table->text('notes')
                ->nullable();

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
