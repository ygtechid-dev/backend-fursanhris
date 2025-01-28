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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->integer('total_leaves');  // Total jatah cuti
            $table->integer('used_leaves');   // Cuti yang sudah dipakai
            $table->integer('remaining_leaves'); // Sisa cuti
            $table->year('year');   // Tahun periode cuti
            $table->integer('carried_forward')->default(0); // Cuti yang dibawa dari tahun sebelumnya
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
