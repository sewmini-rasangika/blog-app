<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\Activity;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->longText('search_text')->nullable()->after('content');
        });

        DB::table('posts')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $text = (new Post(['content' => $row->content]))->plainText();
                DB::table('posts')->where('id', $row->id)->update(['search_text' => $text]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
    }
};
