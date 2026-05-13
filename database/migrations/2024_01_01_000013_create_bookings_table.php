<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('resort_id')->constrained('resorts')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('total_nights');
            $table->integer('guests_adults')->default(1);
            $table->integer('guests_children')->default(0);
            $table->decimal('price_per_night_snapshot', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->text('special_requests')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('bookings'); }
};
