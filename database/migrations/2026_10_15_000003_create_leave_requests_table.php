<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 50)->index();
            $table->string('employee_name');
            $table->date('leave_start_date')->nullable()->index();
            $table->date('leave_end_date')->nullable();
            $table->string('leave_type')->nullable();
            $table->string('leave_type_status', 100)->nullable();
            $table->string('duration', 50)->nullable();
            $table->string('leave_credits', 100)->nullable();
            $table->string('approval_status', 100)->nullable()->index();
            $table->dateTime('raised_on')->nullable();
            $table->text('leave_reason')->nullable();
            $table->string('final_approval')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'leave_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
