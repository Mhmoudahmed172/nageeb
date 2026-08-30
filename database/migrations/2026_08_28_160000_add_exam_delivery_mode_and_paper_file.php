<?php

use App\Enums\ExamDeliveryMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('delivery_mode')->default(ExamDeliveryMode::Interactive->value)->after('status');
            $table->string('file_disk')->nullable()->after('shuffle_options');
            $table->string('file_path')->nullable()->after('file_disk');
            $table->string('file_original_name')->nullable()->after('file_path');
            $table->string('file_mime')->nullable()->after('file_original_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_mime');
            $table->timestamp('file_uploaded_at')->nullable()->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_mode',
                'file_disk',
                'file_path',
                'file_original_name',
                'file_mime',
                'file_size',
                'file_uploaded_at',
            ]);
        });
    }
};
