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
        Schema::create('feeding_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->string('food_name');
            $table->date('fed_at');
            $table->enum('meal_time', ['morning', 'noon', 'evening', 'snack']);
            $table->boolean('is_first_time')->default(false);
            $table->enum('reaction', ['none', 'mild', 'severe'])->default('none');
            $table->text('reaction_note')->nullable();
            $table->string('amount')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();

            $table->index(['child_id', 'food_name', 'fed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeding_records');
    }
};
