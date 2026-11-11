<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('planting_season_id');
            $table->date('received_on');
            $table->string('name', 150);
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['planting_season_id', 'received_on']);
            $table->foreign('planting_season_id')->references('id')->on('planting_seasons')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
