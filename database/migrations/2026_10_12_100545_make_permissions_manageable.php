<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\Activity;

return new class extends Migration
{
    public function up(): void
    {

        if (Schema::hasTable('permissions') && ! Schema::hasColumn('permissions', 'name')) {
            Schema::drop('permissions');
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();          // posts.create
                $table->string('display_name');            // Write and submit posts
                $table->string('description')->nullable();
                $table->string('group')->nullable();       // Posts
                $table->timestamps();
            });
        }

        Schema::table('permissions', function (Blueprint $table) {
            if (! Schema::hasColumn('permissions', 'is_system')) {
                $table->boolean('is_system')->default(false);     // app එකේ code එක පාවිච්චි කරන ඒවා
            }
            if (! Schema::hasColumn('permissions', 'is_sensitive')) {
                $table->boolean('is_sensitive')->default(false);
            }
        });


        DB::table('permissions')
            ->where('is_system', false)
            ->where('name', 'not like', '%.%')
            ->delete();


        $sensitive = config('permissions.sensitive', []);
        $now = now();

        foreach (config('permissions.groups') as $group => $items) {
            foreach ($items as $key => $label) {
                $exists = DB::table('permissions')->where('name', $key)->exists();

                if ($exists) {
                    DB::table('permissions')->where('name', $key)->update(['is_system' => true]);
                } else {
                    DB::table('permissions')->insert([
                        'name' => $key,
                        'display_name' => $label,
                        'group' => $group,
                        'is_system' => true,
                        'is_sensitive' => in_array($key, $sensitive, true),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['is_system', 'is_sensitive']);
        });
    }
};
