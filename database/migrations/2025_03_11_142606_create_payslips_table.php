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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('payslip_number')->unique();
            $table->string('month');
            $table->year('year');
            $table->enum('salary_type', ['monthly', 'hourly'])->default('monthly'); // monthly, hourly
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('total_allowance', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('total_overtime', 15, 2)->default(0);
            $table->json('allowance');
            $table->json('deduction');
            $table->json('overtime');
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid'); // paid, unpaid
            $table->date('payment_date')->nullable();
            $table->enum('payment_method', ['cash', 'bank_transfer'])->nullable(); // bank transfer, cash, etc
            $table->text('note')->nullable();
            $table->string('file_url')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
