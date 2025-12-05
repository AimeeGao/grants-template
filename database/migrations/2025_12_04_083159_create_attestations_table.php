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
        Schema::create('attestations', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique()->index()->comment('Attestation unique identifier');

            // Student Information
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Student user ID');
            $table->uuid('user_guid')->index()->comment('Student user GUID');

            // Institution Information
            $table->string('institution_bceid_guid')->index()->comment('Institution BCeID business GUID');
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('set null');

            // Application Reference
            $table->foreignId('application_id')->nullable()->constrained('applications')->onDelete('set null');

            // Attestation Details
            $table->string('attestation_number')->unique()->comment('PAL number (e.g., PAL-2025-123456)');
            $table->enum('status', [
                'issued',
                'declined',
                'expired',
                'revoked'
            ])->default('issued')->index();

            $table->enum('attestation_type', ['undergraduate', 'graduate'])->index()->comment('Type of attestation');

            // Dates
            $table->date('issue_date')->index()->comment('Date attestation was issued');
            $table->date('expiry_date')->nullable()->index()->comment('Date attestation expires');
            $table->timestamp('declined_at')->nullable()->comment('When attestation was declined');
            $table->timestamp('revoked_at')->nullable()->comment('When attestation was revoked');

            // Decline/Revocation Details
            $table->text('decline_reason')->nullable()->comment('Reason for declining attestation');
            $table->text('revocation_reason')->nullable()->comment('Reason for revoking attestation');
            $table->foreignId('declined_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->uuid('declined_by_user_guid')->nullable()->index();
            $table->uuid('revoked_by_user_guid')->nullable()->index();

            // Additional Information
            $table->string('program_name')->nullable();
            $table->string('program_code')->nullable();
            $table->date('program_start_date')->nullable();
            $table->date('program_end_date')->nullable();
            $table->text('notes')->nullable()->comment('Internal notes');

            // Document References
            $table->string('document_path')->nullable()->comment('Path to PDF attestation document');
            $table->json('metadata')->nullable()->comment('Additional metadata');

            // Audit Fields
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->uuid('issued_by_user_guid')->nullable()->index();
            $table->uuid('created_by_user_guid')->nullable()->index();
            $table->uuid('last_touch_by_user_guid')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['institution_bceid_guid', 'status', 'issue_date']);
            $table->index(['user_id', 'status']);
            $table->index(['attestation_type', 'status']);
            $table->index(['issue_date', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attestations');
    }
};
