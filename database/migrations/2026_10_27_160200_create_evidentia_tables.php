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
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('original_filename');
            $table->string('storage_path');
            $table->longText('document_text')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->decimal('practical_coverage', 5, 2)->default(0.00);
            $table->decimal('ai_supplementary_score', 5, 2)->nullable();
            $table->text('ai_disclaimer')->nullable();
            $table->timestamps();
        });

        Schema::create('paper_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->string('citation_key')->index();
            $table->text('raw_text');
            $table->string('authors')->nullable();
            $table->string('year')->nullable();
            $table->text('title')->nullable();
            $table->string('journal_or_publisher')->nullable();
            $table->string('doi')->nullable()->index();
            $table->text('open_access_url')->nullable();
            $table->string('retrieval_status')->default('NOT_FOUND'); // RETRIEVED, PAYWALLED, NOT_FOUND, ERROR
            $table->text('cached_full_text_path')->nullable();
            $table->timestamps();
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paper_reference_id')->nullable()->constrained('paper_references')->nullOnDelete();
            $table->text('claim_text');
            $table->string('in_text_citation');
            $table->integer('paragraph_index')->default(0);
            $table->string('verdict')->default('UNVERIFIABLE'); // SUPPORTED, PARTIALLY_SUPPORTED, UNSUPPORTED, UNVERIFIABLE
            $table->string('instructor_override_verdict')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('evidence_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->text('evidence_quote')->nullable();
            $table->text('reasoning_warrant')->nullable();
            $table->decimal('confidence_score', 4, 3)->default(0.000);
            $table->string('source_doi')->nullable();
            $table->string('source_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_matches');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('paper_references');
        Schema::dropIfExists('papers');
    }
};
