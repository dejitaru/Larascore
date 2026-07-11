<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('repo_owner');
            $table->string('repo_name');
            $table->string('status')->default('pending'); // pending, analyzing, completed, failed
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('metrics_json')->nullable();
            $table->json('recommendations_json')->nullable();
            $table->string('callback_token');
            $table->timestamps();

            $table->index(['repo_owner', 'repo_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
