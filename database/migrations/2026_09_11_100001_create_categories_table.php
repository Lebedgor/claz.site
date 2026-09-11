<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->jsonb('name');
            $table->jsonb('slug');
            $table->jsonb('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX categories_slug_en_unique ON categories ((slug->>'en'))");
        DB::statement('CREATE INDEX categories_slug_gin ON categories USING gin (slug jsonb_path_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
