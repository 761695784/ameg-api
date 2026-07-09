<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisation_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['realisation_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisation_service');
    }
};
