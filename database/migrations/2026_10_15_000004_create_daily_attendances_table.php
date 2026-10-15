<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('employee_id', 50)->index();
            $table->string('designation')->nullable();
            $table->date('date')->index();
            $table->string('shift_name')->nullable();
            $table->string('scheduled_in_time', 50)->nullable();
            $table->string('scheduled_out_time', 50)->nullable();
            $table->string('actual_punch_in', 50)->nullable();
            $table->string('actual_punch_out', 50)->nullable();
            $table->string('duration', 50)->nullable();
            $table->string('duration_calculation_method', 100)->nullable();
            $table->string('late_in_by', 50)->nullable();
            $table->string('early_out_by', 50)->nullable();
            $table->string('overtime_worked', 50)->nullable();
            $table->string('overtime_eligible', 50)->nullable();
            $table->string('overtime_type', 100)->nullable();
            $table->string('status', 100)->nullable()->index();
            $table->string('payment_status', 100)->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_attendances');
    }
};
