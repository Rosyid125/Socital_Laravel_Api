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
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['userid']);
        });
        
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['userid']);
            $table->dropForeign(['postid']);
        });
        
        Schema::table('likes', function (Blueprint $table) {
            $table->dropForeign(['userid']);
            $table->dropForeign(['postid']);
        });
        
        Schema::table('follows', function (Blueprint $table) {
            $table->dropForeign(['following']);
            $table->dropForeign(['followed']);
        });
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['userid']);
            $table->dropForeign(['trigerrerid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::table('posts', function (Blueprint $table) {
            $table->foreign('userid')->references('userid')->on('users');
        });
        
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('postid')->references('postid')->on('posts');
            $table->foreign('userid')->references('userid')->on('users');
        });
        
        Schema::table('likes', function (Blueprint $table) {
            $table->foreign('userid')->references('userid')->on('users');
            $table->foreign('postid')->references('postid')->on('posts');
        });
        
        Schema::table('follows', function (Blueprint $table) {
            $table->foreign('following')->references('userid')->on('users');
            $table->foreign('followed')->references('userid')->on('users');
        });
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('userid')->references('userid')->on('users');
            $table->foreign('trigerrerid')->references('userid')->on('users');
        });
    }
};
