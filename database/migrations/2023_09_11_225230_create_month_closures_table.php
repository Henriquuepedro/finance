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
        Schema::create('month_closures', function (Blueprint $table) {
            $table->id();
            $table->decimal('spending')->nullable();
            $table->decimal('earnings')->nullable();
            $table->decimal('liquid')->nullable();
            $table->decimal('economy')->nullable();
            $table->decimal('percentage_economy');
            $table->integer('reference_month');
            $table->integer('reference_year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('month_closures');
    }
};
