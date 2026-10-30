<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('planting_season_id');
            $table->date('harvested_at');
            $table->decimal('quantity', 12, 2);
            $table->enum('unit', ['kg', 'ton']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['planting_season_id', 'unit']);
            $table->foreign('planting_season_id')->references('id')->on('planting_seasons')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
