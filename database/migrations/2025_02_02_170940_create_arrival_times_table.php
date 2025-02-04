<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arrival_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses');
            $table->foreignId('stop_id')->constrained('stops');
            $table->time('arrival_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrival_times');
    }
};
