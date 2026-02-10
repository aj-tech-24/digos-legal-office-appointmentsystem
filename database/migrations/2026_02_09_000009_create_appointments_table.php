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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('client_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('lawyer_id')->constrained()->onDelete('cascade');
            $table->foreignId('ai_recommendation_id')->nullable()->constrained()->nullOnDelete();
            
            // Time range scheduling
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->integer('estimated_duration_minutes');
            
            // Case details
            $table->text('narrative');
            $table->text('professional_summary')->nullable();
            $table->json('detected_services')->nullable();
            $table->enum('complexity_level', ['simple', 'moderate', 'complex'])->default('moderate');
            $table->json('document_checklist')->nullable();
            $table->json('documents_submitted')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'pending',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled',
                'no_show',
                'rescheduled'
            ])->default('pending');
            
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Queue management
            $table->integer('queue_number')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();

            $table->index(['start_datetime', 'end_datetime']);
            $table->index('status');
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
