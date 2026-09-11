<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->jsonb('value')->nullable();
            $table->timestamps();

            $table->unique(['tool_id', 'criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_criteria');
    }
};
