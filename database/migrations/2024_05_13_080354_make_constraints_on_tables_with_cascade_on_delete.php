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
            $table->foreign('userid')->references('userid')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('postid')->references('postid')->on('posts')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('userid')->references('userid')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        
        Schema::table('likes', function (Blueprint $table) {
            $table->foreign('userid')->references('userid')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('postid')->references('postid')->on('posts')->onDelete('cascade')->onUpdate('cascade');
        });
        
        Schema::table('follows', function (Blueprint $table) {
            $table->foreign('following')->references('userid')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('followed')->references('userid')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreign('userid')->references('userid')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('trigerrerid')->references('userid')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
        });
    }
};
