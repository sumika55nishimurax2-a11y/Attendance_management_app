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
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->string('field')->nullable();
            $table->foreignId('break_id')->nullable();
            $table->string('before_value')->nullable();
            $table->string('after_value')->nullable();
            $table->text('reason')->nullable()->comment('申請理由');
            $table->timestamp('requested_at')->nullable()->comment('申請日時');
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
