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
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('narrative_hash')->unique(); // Hash of narrative + detected service
            $table->text('original_narrative');
            $table->text('professional_summary')->nullable();
            $table->json('detected_services'); // Primary and secondary services
            $table->enum('complexity_level', ['simple', 'moderate', 'complex'])->default('moderate');
            $table->integer('estimated_duration_minutes')->default(60);
            $table->json('document_checklist')->nullable();
            $table->json('raw_ai_response')->nullable(); // Store full AI response
            $table->timestamps();

            $table->index('narrative_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
