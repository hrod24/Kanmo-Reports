<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('employee_id', 50)->index();
            $table->string('designation')->nullable();
            $table->date('raised_on')->nullable();
            $table->date('raised_for')->nullable()->index();
            $table->string('assigned_shift_name')->nullable();
            $table->string('assigned_in_time', 50)->nullable();
            $table->string('assigned_out_time', 50)->nullable();
            $table->string('assigned_shift_duration', 50)->nullable();
            $table->string('actual_shift_name')->nullable();
            $table->string('actual_in_time', 50)->nullable();
            $table->string('actual_out_time', 50)->nullable();
            $table->string('actual_duration', 50)->nullable();
            $table->string('regularized_shift_name')->nullable();
            $table->string('regularized_in_time', 50)->nullable();
            $table->string('regularized_out_time', 50)->nullable();
            $table->string('regularized_duration', 50)->nullable();
            $table->text('reason')->nullable();
            $table->string('approval_status', 100)->nullable()->index();
            $table->string('action_taken_by_id', 50)->nullable();
            $table->string('action_taken_by_name')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'raised_for']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_regularizations');
    }
};
