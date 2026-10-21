<?php

use App\Support\Panel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Support\Activity;

return new class extends Migration
{
    public function up(): void
    {
      
        foreach (DB::table('users')->orderBy('id')->get(['id', 'name', 'slug']) as $u) {
            if (empty($u->slug) || Panel::isReserved($u->slug)) {
                DB::table('users')->where('id', $u->id)
                    ->update(['slug' => Panel::uniqueSlug($u->name, $u->id)]);
            }
        }
    }

    public function down(): void {}
};
