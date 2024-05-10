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
        Schema::create('follows', function (Blueprint $table) {
            $table->id('followid');
            $table->unsignedBigInteger('following');
            $table->unsignedBigInteger('followed');
            $table->timestamps();

            $table->foreign('following')->references('userid')->on('users');
            $table->foreign('followed')->references('userid')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
