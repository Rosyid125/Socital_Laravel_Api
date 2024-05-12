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
        Schema::dropIfExists('privatemessages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('privatemessages', function (Blueprint $table) {
            $table->id('messageid');
            $table->unsignedBigInteger('senderid');
            $table->unsignedBigInteger('recieverid');
            $table->string('message');
            $table->dateTime('datetime');
            $table->timestamps();

            $table->foreign('senderid')->references('userid')->on('users');
            $table->foreign('recieverid')->references('userid')->on('users');
        });
    }
};
