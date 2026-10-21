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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();            // පෙන්නන නම: "Editor"
            $table->string('slug')->unique();            // users.role column එකට සම්බන්ධ වෙන අගය: "editor" (හදාපු පස්සේ වෙනස් කරන්නේ නැහැ)
            $table->string('description')->nullable();
            $table->json('permissions')->nullable();     // ["posts.create", "posts.approve", ...]
            $table->boolean('is_system')->default(false); // System roles (admin, author) මකන්න බැහැ
            $table->timestamps();
        });

        // දැනට users.role එකේ තියෙන 'admin' සහ 'author' වලට අදාළ roles දෙක
        DB::table('roles')->insert([
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to everything. This role cannot be changed.',
                'permissions' => json_encode([]),            // admin ට code එකෙන්ම හැම permission එකක්ම
                'is_system' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Author',
                'slug' => 'author',
                'description' => 'Default role for registered users. Writes posts for review.',
                'permissions' => json_encode(['posts.create']),
                'is_system' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};