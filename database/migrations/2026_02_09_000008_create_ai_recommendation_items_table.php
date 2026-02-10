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
        Schema::create('ai_recommendation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_recommendation_id')->constrained()->onDelete('cascade');
            $table->foreignId('lawyer_id')->constrained()->onDelete('cascade');
            $table->decimal('match_score', 5, 2); // Overall match percentage
            $table->json('score_breakdown'); // Detailed breakdown of scoring factors
            /*
             * Example score_breakdown:
             * {
             *   "specialization_match": 40,
             *   "similar_cases_handled": 12,
             *   "availability_match": 15,
             *   "experience": 8,
             *   "language_match": 10,
             *   "current_workload": 7
             * }
             */
            $table->integer('rank')->default(0);
            $table->timestamps();

            $table->unique(['ai_recommendation_id', 'lawyer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendation_items');
    }
};
