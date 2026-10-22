<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 50)->unique();
            $table->string('display_name')->nullable();
            $table->string('business_unit_code', 30)->nullable();
            $table->string('department_code', 30)->nullable();
            $table->string('employee_level_code', 30)->nullable();
            $table->timestamps();

            $table->foreign('business_unit_code')->references('code')->on('business_units')->nullOnDelete();
            $table->index('department_code');
            $table->index('employee_level_code');
        });
    }
    public function down(): void { Schema::dropIfExists('employees'); }
};
