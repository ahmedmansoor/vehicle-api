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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('manufacturer');
            $table->string('model');
            $table->decimal('engine_capacity', 8, 2);
            $table->integer('seats');

            // Type-specific fields
            $table->decimal('seat_height', 8, 2)->nullable(); // For motorcycles
            $table->decimal('cargo_capacity', 8, 2)->nullable(); // For cars
            $table->decimal('tonnage', 8, 2)->nullable(); // For pickup trucks

            $table->boolean('is_approved')->default(false);
            $table->foreignId('vehicle_type_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
