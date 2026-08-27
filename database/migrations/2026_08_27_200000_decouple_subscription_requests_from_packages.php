<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable()->change();
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->foreign('package_id')
                ->references('id')
                ->on('subscription_packages')
                ->nullOnDelete();
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->restrictOnDelete();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropForeign(['package_id']);
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable(false)->change();
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->foreign('package_id')->references('id')->on('subscription_packages')->cascadeOnDelete();
        });
    }
};
