<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropIndex('subject');
            $table->dropIndex('causer');
        });

        DB::statement("ALTER TABLE `{$tableName}` MODIFY `subject_id` VARCHAR(50) NULL");
        DB::statement("ALTER TABLE `{$tableName}` MODIFY `causer_id` VARCHAR(50) NULL");

        Schema::table($tableName, function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->index(['causer_type', 'causer_id'], 'causer');
        });
    }

    public function down(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropIndex('subject');
            $table->dropIndex('causer');
        });

        DB::statement("ALTER TABLE `{$tableName}` MODIFY `subject_id` BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `{$tableName}` MODIFY `causer_id` BIGINT UNSIGNED NULL");

        Schema::table($tableName, function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->index(['causer_type', 'causer_id'], 'causer');
        });
    }
};
