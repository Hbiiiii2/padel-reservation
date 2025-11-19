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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_code')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('padel_court_id')->constrained()->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('duration_hours');
            $table->decimal('price_per_hour', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('player_names')->nullable();
            $table->integer('number_of_players')->default(2);
            $table->boolean('equipment_rental')->default(false);
            $table->decimal('equipment_fee', 8, 2)->default(0);
            $table->timestamps();
            
            // Index untuk performance
            $table->index(['padel_court_id', 'start_time', 'end_time']);
            $table->index(['status', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
