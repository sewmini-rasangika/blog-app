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
            Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('author')->index();
    });

 
    $first = DB::table('users')->min('id');
    if ($first) {
        DB::table('users')->where('id', $first)->update(['role' => 'admin']);
    }
}
  


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
