<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resort_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resort_id')->constrained('resorts')->onDelete('cascade');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
        });
    }
    public function down(): void { Schema::dropIfExists('resort_locations'); }
};
