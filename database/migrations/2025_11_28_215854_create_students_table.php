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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->index()->unique();

            $table->uuid('user_guid')->index();
            $table->foreign('user_guid')->references('guid')->on('users')
                ->onDelete('cascade');

            $table->bigInteger('sin')->nullable()->comment('Social insurance number.')->index();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->date('dob')->nullable();
            $table->string('gender', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code')->nullable();
            $table->boolean('bc_resident')->default(false);
            $table->boolean('info_consent')->default(false);


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
