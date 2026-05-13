<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resort_id')->constrained('resorts')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('room_type')->nullable(); // deluxe, suite, standard
            $table->integer('max_occupancy')->default(2);
            $table->integer('total_units')->default(1);
            $table->decimal('price_per_night', 10, 2);
            $table->integer('size_sqft')->nullable();
            $table->string('bed_type')->nullable(); // single, double, king
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('rooms'); }
};
