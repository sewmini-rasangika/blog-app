<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\Activity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
           Schema::table('posts', function (Blueprint $table) {
        $table->string('status')->default('pending');  
        $table->timestamp('approved_at')->nullable();
        $table->index(['status', 'created_at']);
        $table->index(['user_id', 'created_at']);
    });

    DB::table('posts')->update(['status' => 'approved', 'approved_at' => now()]);
}

    
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        $table->dropIndex(['user_id', 'created_at']);
        $table->dropColumn(['status', 'approved_at']);
        });
    }
};

