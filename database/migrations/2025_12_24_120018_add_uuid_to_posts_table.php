<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });

        // Backfill existing posts
        DB::table('posts')->orderBy('id')->chunk(100, function ($posts) {
            foreach ($posts as $post) {
                DB::table('posts')
                    ->where('id', $post->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->string('uuid')->nullable(false)->change();
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
