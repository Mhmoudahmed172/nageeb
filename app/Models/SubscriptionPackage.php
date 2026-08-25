<?php

namespace App\Models;

use App\Enums\StudentRegion;
use Database\Factories\SubscriptionPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_id', 'name', 'price_gaza', 'price_west_bank_abroad', 'duration_label'])]
class SubscriptionPackage extends Model
{
    /** @use HasFactory<SubscriptionPackageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price_gaza' => 'decimal:2',
            'price_west_bank_abroad' => 'decimal:2',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subscriptionRequests(): HasMany
    {
        return $this->hasMany(SubscriptionRequest::class, 'package_id');
    }

    public function priceFor(StudentRegion $region): string
    {
        return $region === StudentRegion::Gaza
            ? $this->price_gaza
            : $this->price_west_bank_abroad;
    }
}
