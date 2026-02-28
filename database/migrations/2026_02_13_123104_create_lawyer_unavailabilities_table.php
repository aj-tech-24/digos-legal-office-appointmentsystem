<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lawyer_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained()->onDelete('cascade');
            $table->date('unavailable_date');
            $table->string('reason');
            $table->boolean('is_whole_day')->default(true);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();  
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lawyer_unavailabilities');
    }
};