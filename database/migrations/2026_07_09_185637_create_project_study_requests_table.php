<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_study_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('city')->nullable();
            $table->string('establishment_type')->nullable(); // hôtel, restaurant, boulangerie...
            $table->longText('description');
            $table->string('estimated_budget')->nullable();
            $table->string('desired_deadline')->nullable();
            $table->enum('status', ['nouveau', 'en_cours', 'traite'])->default('nouveau');
            $table->text('admin_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_study_requests');
    }
};
