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
    Schema::table('appointments', function (Blueprint $table) {
        // Magdugang ta og admin_notes column human sa document_checklist
        $table->text('admin_notes')->nullable()->after('document_checklist');
    });
}

    public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropColumn('admin_notes');
    });
}
};
