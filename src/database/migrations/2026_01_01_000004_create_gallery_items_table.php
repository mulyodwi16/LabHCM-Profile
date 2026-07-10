<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('caption')->nullable();
            $t->string('image_path');
            $t->date('taken_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('gallery_items'); }
};
