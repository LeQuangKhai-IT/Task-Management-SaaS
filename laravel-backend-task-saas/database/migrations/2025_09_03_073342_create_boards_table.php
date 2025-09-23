<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. OK
     */
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('title');
            $table->text('slug');
            $table->text('description')->nullable();
            $table->foreignUuid('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->string('background')->nullable();
            $table->enum('visibility', ['private', 'workspace', 'public'])->default('private');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
