<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->jsonb('title')->nullable();
            $table->jsonb('intro')->nullable();
            $table->jsonb('verdict')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('comparison_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comparison_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->decimal('score', 4, 2)->nullable();
            $table->jsonb('verdict')->nullable();
            $table->timestamps();
        });

        Schema::create('comparison_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comparison_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('criteria')->restrictOnDelete();
            $table->jsonb('value')->nullable();
            $table->jsonb('note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['comparison_item_id', 'criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comparison_scores');
        Schema::dropIfExists('comparison_items');
        Schema::dropIfExists('comparisons');
    }
};
