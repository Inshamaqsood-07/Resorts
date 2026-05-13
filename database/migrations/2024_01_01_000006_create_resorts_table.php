<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, suspended
            $table->string('category')->nullable(); // hotel, resort, villa, etc.
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('resorts'); }
};
