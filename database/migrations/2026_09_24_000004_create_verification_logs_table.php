<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->string('nrp', 25);
            $table->string('employee_name', 100)->nullable();
            $table->string('group_company', 50)->nullable();
            $table->string('company_name', 50)->nullable();
            $table->enum('result', ['active', 'inactive', 'not_found']);
            $table->text('feedback');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'created_at']);
            $table->index('nrp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
