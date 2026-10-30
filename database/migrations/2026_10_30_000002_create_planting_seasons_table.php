<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_seasons', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('land_id');
            $table->string('crop_name', 150);
            $table->date('planted_at');
            $table->date('expected_harvest_at');
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['land_id', 'status']);
            $table->foreign('land_id')->references('id')->on('lands')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_seasons');
    }
};
