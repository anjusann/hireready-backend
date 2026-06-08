<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_description_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cover_letter_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_name');
            $table->string('job_title');
            $table->enum('status', [
                'applied',
                'under_review',
                'interview_scheduled',
                'final_interview',
                'offer_received',
                'rejected',
                'withdrawn',
            ])->default('applied');
            $table->date('applied_date');
            $table->text('notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
