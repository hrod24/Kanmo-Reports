<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->string('business_unit', 150)
                ->nullable()
                ->after('employee_id');

            $table->index('business_unit');
        });
    }

    public function down(): void
    {
        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropIndex(['business_unit']);
            $table->dropColumn('business_unit');
        });
    }
};
