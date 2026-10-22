<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('business_units', function (Blueprint $table) {
            $table->string('code', 30)->primary();
            $table->string('name', 200);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('business_units'); }
};
