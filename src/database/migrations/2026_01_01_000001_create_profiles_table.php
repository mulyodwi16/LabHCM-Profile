<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('photo_path')->nullable();
            $t->string('nrp', 32)->nullable()->index();  // mahasiswa & alumni
            $t->string('nip', 32)->nullable()->index();  // dosen
            $t->string('prodi')->nullable()->index();
            $t->unsignedSmallInteger('angkatan')->nullable()->index();
            $t->string('phone', 32)->nullable();
            $t->text('bio')->nullable();
            $t->json('skills')->nullable();
            $t->string('youtube_url')->nullable();
            $t->string('github_url')->nullable();
            $t->string('portfolio_url')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('profiles'); }
};
