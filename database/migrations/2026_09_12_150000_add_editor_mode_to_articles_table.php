<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('editor_mode', 20)->default('tiptap');
        });

        DB::table('articles')
            ->whereRaw('(body_html::text) like ?', ['%ex-article%'])
            ->update(['editor_mode' => 'html']);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn('editor_mode');
        });
    }
};
