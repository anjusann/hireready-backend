<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resume_analyses', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('user_id');
            $table->foreignId('job_description_id')
                  ->nullable()
                  ->after('resume_id')
                  ->constrained()
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resume_analyses', function (Blueprint $table) {
            $table->dropForeign(['job_description_id']);
            $table->dropColumn(['status', 'job_description_id']);
        });
    }
};