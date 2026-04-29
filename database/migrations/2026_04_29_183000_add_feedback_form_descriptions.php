<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feedback_forms', function (Blueprint $table) {
            $table->text('description_en')->nullable()->after('title_ar');
            $table->text('description_ku')->nullable()->after('description_en');
            $table->text('description_ar')->nullable()->after('description_ku');
        });
    }

    public function down(): void
    {
        Schema::table('feedback_forms', function (Blueprint $table) {
            $table->dropColumn(['description_en', 'description_ku', 'description_ar']);
        });
    }
};
