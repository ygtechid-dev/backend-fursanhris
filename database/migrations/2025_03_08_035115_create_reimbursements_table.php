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
        // Tabel reimbursement utama
        Schema::create('reimbursements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('request_number')->unique()->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('remark')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->timestamp('requested_at');
            $table->date('transaction_date');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->integer('category_id');
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            // $table->foreign('category_id')->references('id')->on('reimbursement_categories');
            $table->foreign('approved_by')->references('id')->on('users');
            $table->foreign('rejected_by')->references('id')->on('users');
            $table->foreign('paid_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reimbursements');
    }
};
