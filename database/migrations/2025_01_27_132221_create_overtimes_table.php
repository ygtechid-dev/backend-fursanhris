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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id');
            $table->string('title')->nullable();
            $table->integer('number_of_days')->nullable();
            $table->date('overtime_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('hours');
            $table->float('rate', 15, 2)->nullable();
            $table->string('type')->nullable();
            $table->string('remark')->nullable();
            $table->integer('created_by');
            $table->timestamps();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->integer('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();


            // $table->foreign('rejected_by')
            //     ->references('id')
            //     ->on('users')
            //     ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
