<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columnNames = config('permission.column_names');
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        foreach (['model_has_permissions', 'model_has_roles'] as $tableName) {
            Schema::table($tableName, function ($table) use ($tableName, $modelKey) {
                $pivotColumn = $tableName === 'model_has_roles' ? 'role_id' : 'permission_id';
                $primaryName = $tableName === 'model_has_roles'
                    ? 'model_has_roles_role_model_type_primary'
                    : 'model_has_permissions_permission_model_type_primary';

                $table->index($pivotColumn, "{$tableName}_{$pivotColumn}_index");
                $table->dropPrimary($primaryName);
                $table->dropIndex("{$tableName}_{$modelKey}_model_type_index");
            });

            DB::statement("ALTER TABLE `{$tableName}` MODIFY `{$modelKey}` VARCHAR(30) NOT NULL");

            Schema::table($tableName, function ($table) use ($tableName, $modelKey) {
                $primaryColumns = $tableName === 'model_has_roles'
                    ? ['role_id', $modelKey, 'model_type']
                    : ['permission_id', $modelKey, 'model_type'];

                $table->index([$modelKey, 'model_type'], "{$tableName}_{$modelKey}_model_type_index");
                $table->primary($primaryColumns, $tableName === 'model_has_roles'
                    ? 'model_has_roles_role_model_type_primary'
                    : 'model_has_permissions_permission_model_type_primary');
            });
        }
    }

    public function down(): void
    {
        $columnNames = config('permission.column_names');
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        foreach (['model_has_permissions', 'model_has_roles'] as $tableName) {
            Schema::table($tableName, function ($table) use ($tableName, $modelKey) {
                $pivotColumn = $tableName === 'model_has_roles' ? 'role_id' : 'permission_id';
                $primaryName = $tableName === 'model_has_roles'
                    ? 'model_has_roles_role_model_type_primary'
                    : 'model_has_permissions_permission_model_type_primary';

                $table->dropIndex("{$tableName}_{$pivotColumn}_index");
                $table->dropPrimary($primaryName);
                $table->dropIndex("{$tableName}_{$modelKey}_model_type_index");
            });

            DB::statement("ALTER TABLE `{$tableName}` MODIFY `{$modelKey}` BIGINT UNSIGNED NOT NULL");

            Schema::table($tableName, function ($table) use ($tableName, $modelKey) {
                $primaryColumns = $tableName === 'model_has_roles'
                    ? ['role_id', $modelKey, 'model_type']
                    : ['permission_id', $modelKey, 'model_type'];

                $table->index([$modelKey, 'model_type'], "{$tableName}_{$modelKey}_model_type_index");
                $table->primary($primaryColumns, $tableName === 'model_has_roles'
                    ? 'model_has_roles_role_model_type_primary'
                    : 'model_has_permissions_permission_model_type_primary');
            });
        }
    }
};
