<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\Activity;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->string('voter_key', 60);
        $table->string('type', 10);
        $table->timestamps();

        $table->unique(['post_id', 'voter_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};