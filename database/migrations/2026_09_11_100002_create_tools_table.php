<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->index();
            $table->string('status', 32)->default('draft')->index();
            $table->jsonb('name');
            $table->jsonb('slug');
            $table->string('vendor')->nullable();
            $table->jsonb('description')->nullable();
            $table->string('logo')->nullable();
            $table->decimal('rating_avg', 4, 2)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("CREATE UNIQUE INDEX tools_slug_en_unique ON tools ((slug->>'en'))");
        DB::statement('CREATE INDEX tools_slug_gin ON tools USING gin (slug jsonb_path_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
