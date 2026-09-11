<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->jsonb('name');
            $table->jsonb('slug');
            $table->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX tags_slug_en_unique ON tags ((slug->>'en'))");
        DB::statement('CREATE INDEX tags_slug_gin ON tags USING gin (slug jsonb_path_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
