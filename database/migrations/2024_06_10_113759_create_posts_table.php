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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('task_id')->nullable()->constrained('tasks');
            $table->string('title');
            $table->string('slug')->unique();
            $table->integer('type')->default(1);
            $table->longText('content')->nullable();
            $table->tinyInteger('status')->default(0)->index(); // Post status default {0} Draft
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('total_like')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
