<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix: Change expires_at from TIMESTAMP to DATETIME to prevent
     * timezone double-conversion issues with Eloquent when app timezone
     * differs from UTC.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE booking_drafts MODIFY expires_at DATETIME NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE booking_drafts MODIFY expires_at TIMESTAMP NOT NULL');
    }
};
