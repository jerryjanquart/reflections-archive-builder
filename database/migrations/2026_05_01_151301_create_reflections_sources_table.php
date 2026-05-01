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
        Schema::create('reflections_sources', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('url')->unique();
            $table->dateTime('post_date')->nullable();
            $table->string('status')->default('imported'); // imported, processed, skipped, failed, needs_review
            $table->integer('files_created')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reflections_sources');
    }
};
