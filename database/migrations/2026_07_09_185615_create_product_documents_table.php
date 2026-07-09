<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['fiche_technique', 'manuel_utilisateur', 'documentation', 'autre'])
                ->default('fiche_technique');
            $table->string('name');
            $table->string('path');
            $table->string('source_pdf')->nullable(); // traçabilité import (PDF fournisseur d'origine)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_documents');
    }
};
