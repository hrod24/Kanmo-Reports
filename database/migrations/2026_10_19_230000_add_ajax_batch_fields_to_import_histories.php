<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('import_histories', function (Blueprint $table) {
            $table->string('report_key', 64)->nullable();
            $table->string('stored_path')->nullable();
            $table->unsignedInteger('next_row')->default(0);
            // This package also adds total_rows and processed_rows via migration below
            // only if they are not already present from the previous async queue package.
        });
        if (!Schema::hasColumn('import_histories', 'total_rows')) {
            Schema::table('import_histories', function (Blueprint $table) {
                $table->unsignedInteger('total_rows')->default(0);
            });
        }
        if (!Schema::hasColumn('import_histories', 'processed_rows')) {
            Schema::table('import_histories', function (Blueprint $table) {
                $table->unsignedInteger('processed_rows')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('import_histories', function (Blueprint $table) {
            $table->dropColumn(['report_key', 'stored_path', 'next_row']);
        });
        // Leave total_rows and processed_rows intact: older queue migration may own them.
    }
};
