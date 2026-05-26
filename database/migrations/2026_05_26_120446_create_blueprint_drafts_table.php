<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_drafts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('finalized_project_id')->nullable()->constrained('projects')->nullOnDelete();

            $table->string('name', 200);
            $table->string('description', 5000)->nullable();
            $table->string('color', 32)->default('sky');
            $table->string('assignment_type', 16)->default('individual');
            $table->string('status', 32)->default('draft');

            $table->date('start_date');
            $table->date('end_date');

            $table->json('tasks');

            $table->timestamp('invited_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_drafts');
    }
};
