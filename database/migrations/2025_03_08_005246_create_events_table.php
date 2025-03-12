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
        // Tabel untuk jenis event
        // Schema::create('event_types', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('color', 7); // Kode warna hex, contoh: #FF5733
        //     $table->timestamps();
        // });

        // Tabel untuk event
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            // $table->foreignId('event_type_id')->constrained('event_types');
            $table->integer('created_by');
            // $table->boolean('is_all_day')->default(false);
            $table->timestamps();
        });

        // Tabel pivot untuk relasi many-to-many antara event dan employee
        Schema::create('event_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            // $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_employees');
        Schema::dropIfExists('events');
        // Schema::dropIfExists('event_types');
    }
};
