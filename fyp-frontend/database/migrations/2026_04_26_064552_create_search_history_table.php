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
        Schema::create('search_history', function (Blueprint $table) {
            $table->id();

            // Link to the student who made this search
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onDelete('cascade');   // delete history when student is deleted

            // What the student searched for
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->tinyInteger('top_n')->default(3);

            // Full API response stored as JSON — avoids repeating LLM calls
            // for the same query (cache-like behaviour)
            $table->json('top_hits');       // reranked lecturer results
            $table->text('rerank_log');     // vector vs LLM rank table
            $table->longText('reasoning');  // ChatGLM3 explanation text

            // Performance tracking
            $table->float('elapsed_seconds')->nullable();

            $table->timestamps();

            // Index on student_id for fast "show my search history" queries
            $table->index(['student_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_history');
    }
};
