<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ku');
            $table->string('name_ar');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained('colleges')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name_en');
            $table->string('name_ku');
            $table->string('name_ar');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ku');
            $table->string('name_ar');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
        Schema::dropIfExists('colleges');
        Schema::dropIfExists('semesters');
    }
};
