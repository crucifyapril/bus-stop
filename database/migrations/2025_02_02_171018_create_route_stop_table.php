<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes');
            $table->foreignId('stop_id')->constrained('stops');
            $table->integer('position_in_route');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stop');
    }
};
