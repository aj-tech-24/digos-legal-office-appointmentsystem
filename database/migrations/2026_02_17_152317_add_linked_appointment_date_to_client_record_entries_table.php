<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_record_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('client_record_entries', 'linked_booking_date')) {
                $table->date('linked_booking_date')->nullable()->after('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_record_entries', function (Blueprint $table) {
            if (Schema::hasColumn('client_record_entries', 'linked_booking_date')) {
                $table->dropColumn('linked_booking_date');
            }
        });
    }
};