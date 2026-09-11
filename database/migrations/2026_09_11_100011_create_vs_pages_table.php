<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vs_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_a_id')->constrained('tools')->restrictOnDelete();
            $table->foreignId('tool_b_id')->constrained('tools')->restrictOnDelete();
            $table->jsonb('intro')->nullable();
            $table->jsonb('conclusion')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->unique(['tool_a_id', 'tool_b_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vs_pages');
    }
};
