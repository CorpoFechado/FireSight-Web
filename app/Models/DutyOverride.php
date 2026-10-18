<?php

namespace App\Models;

use App\Enums\DutyGroup;
use Carbon\CarbonInterface;
use Database\Factories\DutyOverrideFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $override_id
 * @property int $user_id
 * @property DutyGroup $duty_group
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property string|null $reason
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class DutyOverride extends Model
{
    /** @use HasFactory<DutyOverrideFactory> */
    use HasFactory;

    protected $primaryKey = 'override_id';

    protected $fillable = [
        'user_id',
        'duty_group',
        'starts_on',
        'ends_on',
        'reason',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duty_group' => DutyGroup::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function setStartsOnAttribute(mixed $value): void
    {
        $this->attributes['starts_on'] = $value instanceof CarbonInterface
            ? $value->toDateString()
            : (is_string($value) ? substr($value, 0, 10) : Carbon::parse($value)->toDateString());
    }

    public function setEndsOnAttribute(mixed $value): void
    {
        $this->attributes['ends_on'] = $value instanceof CarbonInterface
            ? $value->toDateString()
            : (is_string($value) ? substr($value, 0, 10) : Carbon::parse($value)->toDateString());
    }

    /**
     * @param  Builder<DutyOverride>  $query
     * @return Builder<DutyOverride>
     */
    public function scopeCoveringWeek(Builder $query, CarbonInterface $weekStart): Builder
    {
        $date = $weekStart->toDateString();

        return $query
            ->where('starts_on', '<=', $date)
            ->where('ends_on', '>=', $date);
    }
}
