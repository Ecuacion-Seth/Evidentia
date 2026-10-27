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
        Schema::table('paper_references', function (Blueprint $table) {
            $table->text('journal_or_publisher')->nullable()->change();
            $table->text('authors')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paper_references', function (Blueprint $table) {
            $table->string('journal_or_publisher')->nullable()->change();
            $table->string('authors')->nullable()->change();
        });
    }
};
