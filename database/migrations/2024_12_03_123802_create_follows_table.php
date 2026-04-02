<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowsTable extends Migration
{
    public function up()
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['follower_user_id', 'following_user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('follows');
    }
}
