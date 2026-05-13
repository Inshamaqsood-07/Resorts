<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resort_amenities', function (Blueprint $table) {
            // No auto-increment id — standard pivot table
            $table->foreignId('resort_id')->constrained('resorts')->onDelete('cascade');
            $table->foreignId('amenity_id')->constrained('amenities')->onDelete('cascade');
            $table->primary(['resort_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resort_amenities');
    }
};
