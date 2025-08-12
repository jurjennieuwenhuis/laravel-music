<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('record_artists', function (Blueprint $table) {
            $table->foreignId('record_id');
            $table->foreignId('artist_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_artists');
    }
};
