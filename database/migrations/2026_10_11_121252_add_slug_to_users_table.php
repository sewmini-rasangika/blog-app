<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\Activity;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        
        $used = [];
        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $u) {
            $base = Str::slug($u->name) ?: 'author-' . $u->id;   
            $slug = $base;
            $i = 2;
            while (in_array($slug, $used, true)) {
                $slug = $base . '-' . $i++;
            }
            $used[] = $slug;

            DB::table('users')->where('id', $u->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
