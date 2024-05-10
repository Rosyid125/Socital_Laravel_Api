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
        Schema::dropIfExists('friends');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('friends', function (Blueprint $table) {
            $table->id('friendshipid');
            $table->unsignedBigInteger('userid1');
            $table->unsignedBigInteger('userid2');
            $table->timestamps();

            $table->foreign('userid1')->references('userid')->on('users');
            $table->foreign('userid2')->references('userid')->on('users');
        });
    }
};
