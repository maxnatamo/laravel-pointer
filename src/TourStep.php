<?php

declare(strict_types=1);

namespace Pointer;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Pointer\Events\TourStepCreated;
use Pointer\Models\StoredTourStep;

class TourStep
{
    /**
     * Gets the name of the tour step.
     *
     * @var string
     */
    public string $name;

    /**
     * Gets the parent tour of the step.
     *
     * @var Tour
     */
    private Tour $tour;

    /**
     * Gets the stored instance of the step.
     *
     * @var StoredTourStep
     */
    private StoredTourStep $step;

    /**
     * Get the ID of the step.
     *
     * @return int
     */
    public final function id(): int
    {
        return $this->step->id;
    }

    /**
     * Get the parent tour of the tour step.
     *
     * @return Tour
     */
    public final function tour(): Tour
    {
        return $this->tour;
    }

    /**
     * Gets the next step in the tour. If there isn't any next step, returns `null`.
     *
     * @return null|TourStep
     */
    public final function next(): ?TourStep
    {
        return $this->tour->steps()->after(fn($item, $key) => $key == $this->name);
    }

    /**
     * Gets the previous step in the tour. If there isn't any previous step, returns `null`.
     *
     * @return null|TourStep
     */
    public final function previous(): ?TourStep
    {
        return $this->tour->steps()->before(fn($item, $key) => $key == $this->name);
    }

    /**
     * Get the stored model associated with the step.
     */
    public function stored(): StoredTourStep
    {
        return $this->step;
    }

    /**
     * Find a tour step by it's ID or name.
     *
     * @param int|string $id ID or name of the tour step.
     * @param Tour $tour The parent tour.
     *
     * @return null|TourStep
     */
    public static final function find(int|string $id, Tour $tour): ?TourStep
    {
        /** @var \Illuminate\Database\Eloquent\Builder<StoredTourStep> $query */
        $query = StoredTourStep::whereRelation('tour', 'id', $tour->id());

        $query = match (true) {
            is_int($id) => $query->where('id', $id),
            is_string($id) => $query->where('name', $id),
        };

        if (!($model = $query->first())) {
            return null;
        }

        return static::createFromStored($model, $tour);
    }

    /**
     * Make a tour step instance from the given step name.
     *
     * @param Tour $tour The parent tour of the step.
     * @param string|class-string<TourStep>|TourStep $step The step to create.
     * @throws \InvalidArgumentException
     *
     * @return TourStep
     */
    public static final function make(Tour $tour, mixed $name): TourStep
    {
        if (!$name) {
            throw new \InvalidArgumentException("Invalid tour step name for tour '{$tour->name}'");
        }

        $stepName = getObjectIdentifier($name);

        $step = match (true) {
            is_string($name) => static::createFromName($name),
            $name instanceof TourStep => $name,
            default => throw new \InvalidArgumentException(
                "Cannot make tour: expected string, class-name of instance of TourStep or TourStep, got {$stepName}."
            )
        };

        if (!($step instanceof TourStep)) {
            throw new \InvalidArgumentException(
                "Cannot make tour step: expected class-name of instance of TourStep, got {$stepName}."
            );
        }

        if (!isset($step->name) || Str::length($step->name) == 0) {
            throw new \InvalidArgumentException("Cannot make tour step: failed to read 'name' from TourStep: {$stepName}");
        }

        $step->tour = $tour;
        $step->step = new StoredTourStep(['name' => $step->name]);

        $step->step->tour()->associate($tour->id());
        $step->step->save();

        TourStepCreated::dispatch($tour, $step);

        return $step;
    }

    /**
     * Make a tour step instance from the given class-name or name.
     *
     * @param string|class-string<TourStep> $name
     *
     * @throws \InvalidArgumentException
     *
     * @return TourStep
     */
    private static function createFromName(string $name): TourStep
    {
        if (Str::length($name) == 0) {
            throw new \InvalidArgumentException("Invalid tour step name: '{$name}'");
        }

        $stepClassName = getModelSubclass($name, TourStep::class);

        /** @var TourStep */
        $step = App::make($stepClassName);
        $step->name ??= $name;

        return $step;
    }

    /**
     * Make a tour step instance from the given stored model.
     *
     * @param StoredTourStep $model
     *
     * @return TourStep
     */
    private static function createFromStored(StoredTourStep $model, Tour $tour): TourStep
    {
        /** @var TourStep */
        $step = App::make(TourStep::class);
        $step->step = $model;
        $step->tour = $tour;
        $step->name = $model->name;

        return $step;
    }
}
