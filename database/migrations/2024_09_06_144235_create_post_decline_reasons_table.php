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
        Schema::create('post_decline_reasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained();
            $table->text('reason');
            $table->bigInteger('declined_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_decline_reasons');
    }
};
