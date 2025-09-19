<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->string('field')->nullable();
            $table->foreignId('break_id')->nullable();
            $table->string('before_value')->nullable();
            $table->string('after_value')->nullable();
            $table->text('reason')->nullable()->constrained('break_times')->cascadeOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->string('status', 20)->default('normal')->comment('申請ステータス');
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
