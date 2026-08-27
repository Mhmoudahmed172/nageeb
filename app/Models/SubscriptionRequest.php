<?php

namespace App\Models;

use App\Enums\SubscriptionRequestStatus;
use Database\Factories\SubscriptionRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'student_id',
    'course_id',
    'package_id',
    'access_plan_id',
    'receipt_image_path',
    'status',
    'rejection_reason',
    'reviewed_at',
])]
class SubscriptionRequest extends Model
{
    /** @use HasFactory<SubscriptionRequestFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => SubscriptionRequestStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }

    public function accessPlan(): BelongsTo
    {
        return $this->belongsTo(AccessPlan::class);
    }

    public function receiptUrl(): string
    {
        return Storage::disk('public')->url($this->receipt_image_path);
    }

    public function receiptIsPdf(): bool
    {
        return str_ends_with(strtolower($this->receipt_image_path), '.pdf');
    }
}
