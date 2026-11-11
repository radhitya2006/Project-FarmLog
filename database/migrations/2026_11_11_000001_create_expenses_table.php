<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('planting_season_id');
            $table->date('spent_on');
            $table->string('name', 150);
            $table->enum('category', ['bibit', 'pupuk', 'pestisida', 'upah', 'irigasi', 'sewa', 'transportasi', 'lainnya']);
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['planting_season_id', 'spent_on']);
            $table->foreign('planting_season_id')->references('id')->on('planting_seasons')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
