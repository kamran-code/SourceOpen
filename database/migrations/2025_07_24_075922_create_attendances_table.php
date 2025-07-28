<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->string('student_id');
            $table->string('event_id');
            $table->date('date');
            $table->timestamp('checkin_at')->nullable();
            $table->timestamp('checkout_at')->nullable();

            $table->timestamps();

            // Prevent duplicate attendance per event per day
            $table->unique(['student_id', 'event_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
