<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterAppointmentsStatusEnumAddOngoing extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL only: alter ENUM to add 'ongoing'
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending','confirmed','in_progress','ongoing','completed','cancelled','no_show','rescheduled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'ongoing' from ENUM
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('pending','confirmed','in_progress','completed','cancelled','no_show','rescheduled') DEFAULT 'pending'");
    }
}
