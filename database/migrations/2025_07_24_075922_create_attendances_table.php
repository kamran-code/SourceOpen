<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('attendances', function (Blueprint $table) {
        $table->id();

        $table->string('student_id');
        $table->string('student_name');

        $table->date('date'); // daily tracking
        $table->timestamp('marked_at')->useCurrent();

        $table->unique(['student_id', 'date']); // no duplicates per day
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
