<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class PositionSwap
{
    /**
     * @param  callable(): int  $maxPosition
     */
    public static function adjacent(Model $item, ?Model $swap, callable $maxPosition): void
    {
        if (! $swap) {
            return;
        }

        DB::transaction(function () use ($item, $swap, $maxPosition) {
            $first = $item->position;
            $second = $swap->position;
            $temp = $maxPosition() + 1;

            $item->update(['position' => $temp]);
            $swap->update(['position' => $first]);
            $item->update(['position' => $second]);
        });
    }
}
