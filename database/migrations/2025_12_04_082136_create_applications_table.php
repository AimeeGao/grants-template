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
            $table->uuid('guid')->unique()->index()->comment('Application unique identifier');

            // Student Information
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Student user ID');
            $table->uuid('user_guid')->index()->comment('Student user GUID');

            // Institution Information
            $table->string('institution_bceid_guid')->index()->comment('Institution BCeID business GUID');
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('set null');

            // Application Details
            $table->string('application_number')->unique()->comment('Human-readable application number');
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'additional_info_required',
                'approved',
                'rejected',
                'withdrawn',
                'expired'
            ])->default('draft')->index();

            $table->enum('program_level', ['undergraduate', 'graduate'])->index();
            $table->string('program_name')->nullable();
            $table->string('program_code')->nullable();
            $table->date('program_start_date')->nullable();
            $table->date('program_end_date')->nullable();

            // Financial Information
            $table->decimal('requested_amount', 10, 2)->nullable()->comment('Amount requested by student');
            $table->decimal('approved_amount', 10, 2)->nullable()->comment('Amount approved by institution');

            // Dates
            $table->timestamp('submitted_at')->nullable()->index()->comment('When student submitted application');
            $table->timestamp('reviewed_at')->nullable()->comment('When institution reviewed application');
            $table->timestamp('approved_at')->nullable()->comment('When application was approved');
            $table->timestamp('rejected_at')->nullable()->comment('When application was rejected');
            $table->timestamp('withdrawn_at')->nullable()->comment('When student withdrew application');

            // Review Information
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->uuid('reviewed_by_user_guid')->nullable()->index();
            $table->text('review_notes')->nullable()->comment('Internal notes from institution reviewer');
            $table->text('rejection_reason')->nullable()->comment('Reason for rejection if applicable');
            $table->text('student_notes')->nullable()->comment('Notes from student');

            // Additional Fields
            $table->boolean('attestation_required')->default(true)->comment('Whether PAL attestation is required');
            $table->foreignId('attestation_id')->nullable()->comment('Linked attestation if issued');
            $table->json('supporting_documents')->nullable()->comment('Array of document references');

            // Audit Fields
            $table->uuid('created_by_user_guid')->nullable()->index();
            $table->uuid('last_touch_by_user_guid')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['institution_bceid_guid', 'status', 'submitted_at']);
            $table->index(['user_id', 'status']);
            $table->index(['program_level', 'status']);
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
