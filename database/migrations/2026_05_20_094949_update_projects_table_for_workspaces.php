<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('projects', 'parent_id')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $foreignKeys = collect(DB::select(
                    'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                    ['projects', 'parent_id']
                ))->pluck('CONSTRAINT_NAME');

                foreach ($foreignKeys as $name) {
                    DB::statement("ALTER TABLE `projects` DROP FOREIGN KEY `{$name}`");
                }

                $indexExists = collect(DB::select('SHOW INDEX FROM `projects` WHERE Key_name = ?', ['projects_user_id_index']))->isNotEmpty();

                if (! $indexExists) {
                    DB::statement('ALTER TABLE `projects` ADD INDEX `projects_user_id_index` (`user_id`)');
                }
            }

            Schema::table('projects', function (Blueprint $table) use ($driver) {
                if ($driver !== 'mysql' && $driver !== 'mariadb') {
                    try {
                        $table->dropForeign(['parent_id']);
                    } catch (Throwable $e) {
                    }
                    try {
                        $table->dropIndex(['user_id', 'parent_id']);
                    } catch (Throwable $e) {
                    }
                }
                $table->dropColumn('parent_id');
            });
        }

        if (! Schema::hasColumn('projects', 'workspace_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('workspace_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('workspaces')
                    ->nullOnDelete();

                $table->index(['user_id', 'workspace_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('projects', 'workspace_id')) {
            Schema::table('projects', function (Blueprint $table) {
                try {
                    $table->dropForeign(['workspace_id']);
                } catch (Throwable $e) {
                }
                try {
                    $table->dropIndex(['user_id', 'workspace_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('workspace_id');
            });
        }

        if (! Schema::hasColumn('projects', 'parent_id')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('projects')
                    ->cascadeOnDelete();

                $table->index(['user_id', 'parent_id']);
            });
        }
    }
};
