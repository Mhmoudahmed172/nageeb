<?php

namespace App\Models;

use Database\Factories\AccessPlanPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['access_plan_id', 'region_id', 'price', 'sale_price', 'currency'])]
class AccessPlanPrice extends Model
{
    /** @use HasFactory<AccessPlanPriceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    public function accessPlan(): BelongsTo
    {
        return $this->belongsTo(AccessPlan::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function effectivePrice(): string
    {
        return $this->sale_price ?? $this->price;
    }
}
