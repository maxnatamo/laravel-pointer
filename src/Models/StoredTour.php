<?php

declare(strict_types=1);

namespace Pointer\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Pointer\TourStatus;

/**
 * @property-read   int $id
 * @property-read   string $name
 * @property        TourStatus $status
 * @property        ?mixed $context
 * @property-read   Collection<StoredTourStep> $steps
 * @property        ?\Illuminate\Support\Carbon $completed_at
 */
class StoredTour extends Model
{
    use Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'owner_id',
        'owner_type',
        'status',
        'context',
        'completed_at',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string,mixed>
     */
    protected $attributes = [
        'status' => TourStatus::Created,
        'context' => null,
        'step' => null,
        'completed_at' => null,
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'steps',
        'step',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'status' => TourStatus::class,
            'context' => 'array',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = static::tableName();
    }

    /**
     * Get the current step of the tour.
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(StoredTourStep::class, 'step');
    }

    /**
     * Get the steps for the tour.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(StoredTourStep::class, 'tour_id');
    }

    /**
     * Get the parent owner model.
     */
    public function owner(): ?MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('status', TourStatus::Completed)
            ->where('completed_at', '<=', now()->sub((Config::get('pointer.prune.min_age', '1 week') ?? '0 seconds')));
    }

    /**
     * Prune the model in the database.
     *
     * @return bool|null
     */
    public function prune(): ?bool
    {
        /** @var string */
        $mode = config('pointer.prune.mode', 'shallow');

        if (!in_array($mode, ['shallow', 'full'])) {
            throw new \InvalidArgumentException(
                "Invalid prune mode: should be either 'shallow' or 'full', received '{$mode}'"
            );
        }

        // Remove the current step of the tour, to prevent foreign key constraint errors.
        $this->step()->dissociate();
        $this->save();

        if ($mode === 'shallow') {
            $this->steps()->delete();
        } else {
            $this->delete();
        }

        return true;
    }

    public static function tableName(): string
    {
        return config('pointer.table_names.tours', 'tours');
    }
}
