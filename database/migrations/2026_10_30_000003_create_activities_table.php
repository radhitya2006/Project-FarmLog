<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('planting_season_id');
            $table->string('name', 150);
            $table->date('scheduled_for');
            $table->enum('status', ['direncanakan', 'selesai'])->default('direncanakan');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['planting_season_id', 'status', 'scheduled_for']);
            $table->foreign('planting_season_id')->references('id')->on('planting_seasons')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
