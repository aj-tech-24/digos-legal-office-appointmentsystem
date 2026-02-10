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
        Schema::create('booking_drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->string('client_email')->nullable();
            $table->json('draft_state'); // Stores all step data as JSON
            $table->integer('current_step')->default(1);
            $table->boolean('privacy_accepted')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('session_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_drafts');
    }
};
