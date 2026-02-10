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
        Schema::create('client_record_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
              $table->enum('entry_type', [
                'appointment_created',
                'appointment_confirmed',
                'appointment_started',
                'appointment_completed',
                'appointment_cancelled',
                'appointment_rescheduled',
                'appointment_update',
                'case_note',
                'document_uploaded',
                'document_verified',
                'lawyer_comment',
                'status_change',
                'check_in'
            ]);
            
            $table->string('title');
            $table->text('content')->nullable();
            $table->json('metadata')->nullable(); // Additional data based on entry type
            
            $table->timestamps();

            $table->index('entry_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_record_entries');
    }
};
