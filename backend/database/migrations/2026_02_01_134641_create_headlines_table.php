<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('headlines', static function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('title');
            $table->string('source');
            $table->text('url');
            $table->text('description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('sentiment')->default('neutral');
            $table->decimal('sentiment_score', 3, 2)->default(0);
            $table->json('keywords')->nullable();
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->text('reflection')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('displayed_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('displayed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('headlines');
    }
};
