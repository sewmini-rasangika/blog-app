<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Support\Activity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         if (! DB::table('permissions')->where('name', 'activity.view')->exists()) {
        DB::table('permissions')->insert([
            'name'         => 'activity.view',
            'display_name' => 'View the activity log',
            'group'        => 'Site',
            'is_system'    => true,
            'is_sensitive' => true,
            'created_at'   => now(),
            'updated_at'   => now(),
                ]);
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            DB::table('permissions')->where('name', 'activity.view')->delete();
    }
};
