<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('employee_bands', function (Blueprint $table) {
            $table->string('code', 30)->primary();
            $table->string('name', 30);
            $table->string('source', 40)->default('HEAD OFFICE');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('employee_bands'); }
};
