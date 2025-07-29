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
        Schema::create('ai_generated_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider')->index();
            $table->text('prompt');
            $table->json('options')->nullable();
            $table->string('model')->nullable();
            $table->string('original_url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->json('thumbnails')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('cost', 8, 4)->nullable();
            $table->string('user_id')->nullable()->index();
            $table->string('session_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['provider', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generated_images');
    }
};
