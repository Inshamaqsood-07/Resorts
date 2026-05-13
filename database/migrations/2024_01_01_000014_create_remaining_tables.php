<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // approved, rejected, suspended
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('sender_phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('unread'); // unread, read, replied
            $table->foreignId('replied_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reply_text')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type')->nullable(); // booking, resort, user
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_approval_logs');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('notifications');
    }
};
