<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['daily_attendances','on_duties','leave_requests','attendance_regularizations'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('source_file')->nullable();
            });
        }
        foreach (['leave_requests','attendance_regularizations'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('source_key', 64)->nullable()->unique();
            });
        }
    }
    public function down(): void
    {
        foreach (['leave_requests','attendance_regularizations'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['source_key']);
                $table->dropColumn('source_key');
            });
        }
        foreach (['daily_attendances','on_duties','leave_requests','attendance_regularizations'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('source_file');
            });
        }
    }
};
