<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blueprint_draft_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blueprint_draft_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name', 120);
            $table->string('email', 191)->nullable();
            $table->string('skills', 500)->nullable();
            $table->unsignedTinyInteger('split')->nullable();

            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();

            $table->timestamps();

            $table->index(['blueprint_draft_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blueprint_draft_members');
    }
};
