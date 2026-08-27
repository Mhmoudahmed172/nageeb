<?php

use App\Enums\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('live');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        $now = now();
        $gazaId = DB::table('regions')->insertGetId([
            'name' => 'غزة',
            'code' => 'gaza',
            'status' => 'live',
            'position' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $westBankId = DB::table('regions')->insertGetId([
            'name' => 'الضفة الغربية',
            'code' => 'west_bank',
            'status' => 'live',
            'position' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('user_id')->constrained('regions')->restrictOnDelete();
        });

        if (Schema::hasColumn('student_profiles', 'region')) {
            foreach (DB::table('student_profiles')->get() as $profile) {
                $code = $profile->region === 'gaza' ? 'gaza' : 'west_bank';
                $regionId = $code === 'gaza' ? $gazaId : $westBankId;
                DB::table('student_profiles')->where('user_id', $profile->user_id)->update([
                    'region_id' => $regionId,
                ]);
            }

            Schema::table('student_profiles', function (Blueprint $table) {
                $table->dropColumn('region');
            });
        }

        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('status')->default(ContentStatus::Live->value);
            $table->timestamps();

            $table->unique(['course_id', 'position']);
            $table->index('status');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('semester_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        foreach (DB::table('courses')->orderBy('id')->get() as $course) {
            $semesterId = DB::table('semesters')->insertGetId([
                'course_id' => $course->id,
                'title' => 'الفصل الدراسي',
                'description' => null,
                'position' => 1,
                'status' => ContentStatus::Live->value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (Schema::hasColumn('units', 'course_id')) {
                DB::table('units')->where('course_id', $course->id)->update([
                    'semester_id' => $semesterId,
                ]);
            }
        }

        if (Schema::hasColumn('units', 'course_id')) {
            Schema::table('units', function (Blueprint $table) {
                $table->dropUnique(['course_id', 'position']);
            });

            Schema::table('units', function (Blueprint $table) {
                $table->dropConstrainedForeignId('course_id');
            });
        }

        Schema::table('units', function (Blueprint $table) {
            $table->unique(['semester_id', 'position']);
        });

        Schema::create('access_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default(ContentStatus::Live->value);
            $table->unsignedInteger('access_duration_days')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'status']);
        });

        Schema::create('access_plan_semester', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['access_plan_id', 'semester_id']);
        });

        Schema::create('access_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->decimal('price', 8, 2);
            $table->decimal('sale_price', 8, 2)->nullable();
            $table->string('currency', 8)->default('ILS');
            $table->timestamps();

            $table->unique(['access_plan_id', 'region_id']);
        });

        Schema::create('lesson_content_region', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lesson_content_id', 'region_id']);
        });

        foreach (DB::table('subscription_packages')->orderBy('id')->get() as $package) {
                $planId = DB::table('access_plans')->insertGetId([
                    'course_id' => $package->course_id,
                    'title' => $package->name,
                    'description' => $package->duration_label,
                    'status' => ContentStatus::Live->value,
                    'access_duration_days' => null,
                    'created_at' => $package->created_at,
                    'updated_at' => $package->updated_at,
                ]);

                foreach (DB::table('semesters')->where('course_id', $package->course_id)->pluck('id') as $semesterId) {
                    DB::table('access_plan_semester')->insert([
                        'access_plan_id' => $planId,
                        'semester_id' => $semesterId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                DB::table('access_plan_prices')->insert([
                    [
                        'access_plan_id' => $planId,
                        'region_id' => $gazaId,
                        'price' => $package->price_gaza,
                        'currency' => 'ILS',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    [
                        'access_plan_id' => $planId,
                        'region_id' => $westBankId,
                        'price' => $package->price_west_bank_abroad,
                        'currency' => 'ILS',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                ]);
        }

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->foreignId('access_plan_id')->nullable()->after('package_id')->constrained('access_plans')->nullOnDelete();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'course_id']);
            $table->foreignId('access_plan_id')->nullable()->after('course_id')->constrained('access_plans')->restrictOnDelete();
            $table->foreignId('region_id')->nullable()->after('access_plan_id')->constrained('regions')->restrictOnDelete();
            $table->decimal('amount_paid', 8, 2)->nullable()->after('region_id');
            $table->string('currency', 8)->nullable()->after('amount_paid');
            $table->timestamp('starts_at')->nullable()->after('granted_at');
            $table->string('status')->default('active')->after('expires_at');
            $table->index(['student_id', 'access_plan_id']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('access_plan_id');
            $table->dropConstrainedForeignId('region_id');
            $table->dropColumn(['amount_paid', 'currency', 'starts_at']);
            $table->unique(['student_id', 'course_id']);
        });

        Schema::table('subscription_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('access_plan_id');
        });

        Schema::dropIfExists('lesson_content_region');
        Schema::dropIfExists('access_plan_prices');
        Schema::dropIfExists('access_plan_semester');
        Schema::dropIfExists('access_plans');

        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::dropIfExists('semesters');

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('region')->default('gaza');
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::dropIfExists('regions');
    }
};
