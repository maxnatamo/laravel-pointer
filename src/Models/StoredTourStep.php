<?php

namespace Pointer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pointer\Models\StoredTour;

/**
 * @property-read   int $id
 * @property-read   string $name
 * @property-read   StoredTour $tour
 */
class StoredTourStep extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = static::tableName();
    }

    /**
     * Get the tour associated with the step.
     */
    public function tour(): BelongsTo
    {
        return $this->belongsTo(StoredTour::class, 'tour_id');
    }

    public static function tableName(): string
    {
        return config('pointer.table_names.tour_steps', 'tour_steps');
    }
}
