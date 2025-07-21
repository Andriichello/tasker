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
            $table->foreignId('user_id');
            // title, description, status, created_at, and visibility (public/private).
            // status: to-do, in-progress, done, canceled
            $table->string('status', 25);
            $table->string('title');
            $table->text('description');
            $table->string('visibility', 10);
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('visibility');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
