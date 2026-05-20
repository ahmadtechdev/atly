<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'project_id')) {
                $table->foreignId('project_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('projects')
                    ->nullOnDelete();
            } else {
                $table->foreign('project_id')
                    ->references('id')
                    ->on('projects')
                    ->nullOnDelete();
            }

            $table->index(['user_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropIndex(['user_id', 'project_id']);
            $table->dropColumn('project_id');
        });
    }
};
