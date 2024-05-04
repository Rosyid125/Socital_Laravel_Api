<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id('likeid');
            $table->unsignedBigInteger('userid');
            $table->unsignedBigInteger('postid');
            $table->timestamps();

            $table->foreign('userid')->references('userid')->on('users');
            $table->foreign('postid')->references('postid')->on('posts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
