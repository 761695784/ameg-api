<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_characteristics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('groupe');          // ex: Tambour, Puissance, Général, Dimensions...
            $table->string('caracteristique');  // ex: Capacité (charge 1/10)
            $table->string('valeur');
            $table->string('unite')->nullable(); // ex: kg, L, kW, tr/min
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'groupe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_characteristics');
    }
};
