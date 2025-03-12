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
        Schema::create('terminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('termination_type');
            $table->date('termination_date');
            $table->text('description')->nullable();
            $table->string('reason')->nullable();
            $table->date('notice_date')->nullable();
            $table->unsignedBigInteger('terminated_by')->nullable();
            $table->boolean('is_mobile_access_allowed')->default(false);
            $table->string('status')->default('active');
            $table->foreignId('company_id')->constrained('users')->onDelete('cascade');
            $table->json('documents')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('terminated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminations');
    }
};
