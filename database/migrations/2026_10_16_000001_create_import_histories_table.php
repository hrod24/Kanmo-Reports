<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_histories', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 64);
            $table->string('original_filename');
            $table->unsignedBigInteger('file_size');
            $table->string('status', 20); // success | failed
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['created_at', 'report_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_histories');
    }
};
