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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // leave_request, attendance_reminder, payroll_info, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // additional data as JSON
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_important')->default(false);
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('category')->default('general'); // general, leave, attendance, payroll, announcement
            $table->string('action_url')->nullable(); // deep link for mobile app
            $table->json('metadata')->nullable(); // additional metadata
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
