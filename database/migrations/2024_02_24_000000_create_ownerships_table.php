<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ownable_ownerships', function (Blueprint $table) {
            $table->id();
            $table->string('model_class')->index();
            $table->string('model_short')->index();
            $table->unsignedBigInteger('record_id')->index();
            $table->unsignedBigInteger('owner_id')->index();
            $table->string('owner_class')->index();
            $table->timestamps();

            $table->unique(['model_class', 'model_short', 'record_id', 'owner_id', 'owner_class'], 'ownership_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ownable_ownerships');
    }
};
