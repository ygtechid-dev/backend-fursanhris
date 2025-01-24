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
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('name');
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('email');
            $table->string('password');

            $table->string('employee_id');
            $table->integer('branch_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('designation_id')->nullable();
            $table->string('company_doj')->nullable();
            $table->string('documents')->nullable();

            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_identifier_code')->nullable();
            $table->string('branch_location')->nullable();
            $table->string('tax_payer_id')->nullable();
            $table->integer('salary_type')->nullable();
            $table->integer('account_type')->nullable();
            $table->float('salary', 20, 2)->default(0.00);
            $table->integer('is_active')->default('1');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
