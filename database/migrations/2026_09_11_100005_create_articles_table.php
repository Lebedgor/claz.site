<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('title');
            $table->jsonb('slug');
            $table->jsonb('excerpt')->nullable();
            $table->jsonb('body_html')->nullable();
            $table->string('cover')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedSmallInteger('reading_time')->nullable();
            $table->jsonb('meta_title')->nullable();
            $table->jsonb('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE UNIQUE INDEX articles_slug_en_unique ON articles ((slug->>'en'))");
        DB::statement('CREATE INDEX articles_slug_gin ON articles USING gin (slug jsonb_path_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
