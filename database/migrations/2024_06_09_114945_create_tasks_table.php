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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assign_user_id')->constrained('users');
            $table->string('subject', 255);
            $table->text('note')->nullable();
            $table->tinyInteger('status')->default(0)->index(); // status default {0} Pending
            $table->boolean('notify_before_deadline')->default(false)->index();
            $table->dateTime('due_date')->index();
            $table->foreignId('assign_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
