<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('on_duties', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('employee_id', 50)->index();
            $table->string('designation')->nullable();
            $table->date('raised_on')->nullable();
            $table->date('raised_for')->nullable()->index();
            $table->string('request_id', 100)->nullable()->index();
            $table->string('shift_name')->nullable();
            $table->string('on_duty_type', 100)->nullable();
            $table->string('on_duty_start_time', 50)->nullable();
            $table->string('on_duty_end_time', 50)->nullable();
            $table->string('on_duty_period', 50)->nullable();
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
        Schema::dropIfExists('on_duties');
    }
};
