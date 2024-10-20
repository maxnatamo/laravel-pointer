<?php

declare(strict_types=1);

namespace Pointer;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Pointer\Events\TourStatusUpdated;
use Pointer\Events\TourStepUpdated;
use Pointer\TourStatus;
use Pointer\Models\StoredTour;
use Pointer\Models\StoredTourStep;
use Pointer\Traits\HasOwner;
use Pointer\Traits\Tourable;
use Pointer\TourStep;

class Tour
{
    /**
     * Gets the name of the tour.
     *
     * @var string
     */
    public string $name;

    /**
     * Gets the list of steps within the tour.
     *
     * @var array<string|class-string<TourStep>|TourStep>
     */
    protected array $steps = [];

    /**
     * Gets the stored instance of the tour.
     *
     * @var StoredTour
     */
    private StoredTour $tour;

    /**
     * Gets the steps of the tour, compiled into instances of :php:class`\Pointer\TourStep'.
     *
     * @var Collection<string,TourStep>
     */
    private Collection $compiledSteps;

    /**
     * Get the ID of the tour.
     *
     * @return int
     */
    public final function id(): int
    {
        return $this->tour->id;
    }

    /**
     * Get the current status of the tour.
     *
     * @return TourStatus
     */
    public final function status(): TourStatus
    {
        return $this->tour->status;
    }

    /**
     * Set the current status of the tour.
     *
     * @param TourStatus $status
     */
    public final function setStatus(TourStatus $status)
    {
        if ($status == TourStatus::Restarted) {
            $status = TourStatus::Started;
        }

        $this->tour->status = $status;
        $this->tour->save();
    }

    /**
     * Get the current context of the tour.
     *
     * @return mixed
     */
    public final function context(): mixed
    {
        return $this->tour->context;
    }

    /**
     * Set the current context of the tour.
     *
     * @param mixed $context
     */
    public final function setContext(mixed $context)
    {
        $this->tour->context = $context;
        $this->tour->save();
    }

    /**
     * Clear the current context of the tour.
     */
    public final function clearContext()
    {
        $this->setContext(null);
    }

    /**
     * Get the current step in the tour, if any.
     *
     * @return ?TourStep
     */
    public final function current(): ?TourStep
    {
        if (!($step = $this->tour->step()->get()->first())) {
            return null;
        }

        return $this->step($step->name);
    }

    /**
     * Get the instance of `TourStep` in the tour with the given name, if any.
     *
     * @param int|string $step
     *
     * @return ?TourStep
     */
    public final function step(int|string $step): ?TourStep
    {
        return $this->compiledSteps->get($step);
    }

    /**
     * Get all the steps in the tour, as a collection of `TourStep`.
     *
     * @return Collection<string,TourStep>
     */
    public final function steps(): Collection
    {
        return $this->compiledSteps;
    }

    /**
     * Get whether the tour has been completed.
     */
    public final function completed(): bool
    {
        return $this->tour->status == TourStatus::Completed;
    }

    /**
     * Get when the tour was been completed, if it has.
     */
    public final function completedAt(): ?Date
    {
        return $this->tour->completed_at;
    }

    /**
     * Create a new step and add it to the tour.
     *
     * @param string|class-string<TourStep>|TourStep $step
     *
     * @return TourStep
     */
    public final function createStep(mixed $step): TourStep
    {
        $model = TourStep::make($this, $step);

        $this->compiledSteps->put($model->name, $model);

        return $model;
    }

    /**
     * Create new steps and add them to the tour.
     *
     * @param array<string|class-string<TourStep>|TourStep> $steps
     *
     * @return Collection<TourStep>
     */
    public final function createSteps(array $steps): Collection
    {
        return collect($steps)->map(fn($step) => $this->createStep($step));
    }

    /**
     * Create a new step and add it to the tour.
     *
     * @param string|class-string<TourStep>|TourStep $step
     *
     * @return self
     */
    public final function addStep(mixed $step): self
    {
        $this->createStep($step);
        return $this;
    }

    /**
     * Create new steps and add them to the tour.
     *
     * @param array<string|class-string<TourStep>|TourStep> $steps
     *
     * @return self
     */
    public final function addSteps(array $steps): self
    {
        $this->createSteps($steps);
        return $this;
    }

    /**
     * Start the tour at the first step.
     *
     * @param null|int|string|TourStep $step The step-index, -name or -instance or start at.
     * @param bool $force Start the tour, even though it has already started.
     *
     * @return self
     */
    public final function start(null|int|string|TourStep $step = null, bool $force = false): self
    {
        if ($this->tour->status == TourStatus::Started && !$force) {
            return $this;
        }

        TourStatusUpdated::dispatch($this, $this->status(), TourStatus::Started);

        $this->goToStep(match (true) {
            is_null($step) => $this->steps()->first(),
            is_int($step), is_string($step) => $this->step($step),
            $step instanceof TourStep => $step,
        });

        return $this;
    }

    /**
     * Cancel the tour.
     *
     * @return self
     */
    public final function cancel(): self
    {
        TourStatusUpdated::dispatch($this, $this->status(), TourStatus::Cancelled);

        $this->goToStep(null);

        return $this;
    }

    /**
     * Finish the tour and mark it as completed.
     *
     * @return self
     */
    public final function finish(?\Illuminate\Support\Carbon $completedAt = null): self
    {
        TourStatusUpdated::dispatch($this, $this->status(), TourStatus::Completed);

        $this->goToStep(null);

        $this->tour->completed_at = $completedAt ?? Date::now();
        $this->tour->save();

        return $this;
    }

    /**
     * Finish the tour and mark it as failed.
     *
     * @return self
     */
    public final function fail(): self
    {
        TourStatusUpdated::dispatch($this, $this->status(), TourStatus::Failed);

        $this->goToStep(null);

        return $this;
    }

    /**
     * Restart the tour at the first step.
     *
     * @return self
     */
    public final function restart(): self
    {
        TourStatusUpdated::dispatch($this, $this->status(), TourStatus::Restarted);

        $this->goToStep($this->steps()->first());

        return $this;
    }

    /**
     * Navigate the tour to the next step, if any. If the tour is on the last step, nothing is done.
     *
     * @throws \Exception Thrown if the tour isn't mutable.
     * @throws \Exception Thrown if there are no steps within the tour.
     *
     * @return self
     */
    public final function next(): self
    {
        if (!$this->mutableState()) {
            throw new \Exception(
                "Cannot go to next step in tour '{$this->name}': state must be either Created or Started, got '{$this->status()->name}'"
            );
        }

        /** @var TourStep $currentStep */
        $currentStep = $this->current() ?? $this->steps()->first();

        if (!$currentStep) {
            throw new \Exception(
                "Cannot go to next step in tour '{$this->name}': no steps in tour."
            );
        }

        $next_step = $currentStep->next();
        if (!$next_step) {
            $this->goToStep(null);
            TourStatusUpdated::dispatch($this, $this->status(), TourStatus::Completed);

            return $this;
        }

        $this->goToStep($next_step);

        return $this;
    }

    /**
     * Navigate the tour to the previous step, if any. If the tour is on the first step, nothing is done.
     *
     * @throws \Exception Thrown if the tour isn't mutable.
     * @throws \Exception Thrown if there are no steps within the tour.
     *
     * @return self
     */
    public final function previous(): self
    {
        if (!$this->mutableState()) {
            throw new \Exception(
                "Cannot go to previous step in tour '{$this->name}': state must be either Created or Started, got '{$this->status()->name}'"
            );
        }

        /** @var TourStep $currentStep */
        $currentStep = $this->current() ?? $this->steps()->first();

        if (!$currentStep) {
            throw new \Exception(
                "Cannot go to previous step in tour '{$this->name}': no steps in tour."
            );
        }

        $prev_step = $currentStep->previous();
        if (!$prev_step) {
            return $this;
        }

        $this->goToStep($prev_step);

        return $this;
    }

    /**
     * Get the stored model associated with the tour.
     */
    public function stored(): StoredTour
    {
        return $this->tour;
    }

    /**
     * Get whether the tour is in a mutable state.
     *
     * @return boolean
     */
    private function mutableState(): bool
    {
        return in_array($this->status(), array(TourStatus::Created, TourStatus::Started));
    }

    /**
     * Compile the steps in `steps` into a collection of `TourStep`-instances.
     *
     * @return Collection<string,TourStep>
     */
    private function compileSteps(): Collection
    {
        return collect($this->steps)
            ->ensure([StoredTourStep::class, TourStep::class, 'string'])
            ->map(function ($value) {
                if ($value instanceof StoredTourStep) {
                    return TourStep::find($value->id, $this);
                }

                if ($value instanceof TourStep) {
                    return $value;
                }

                return TourStep::make($this, $value);
            })
            ->keyBy('name');
    }

    /**
     * Go to the given setup and associate it as the current step.
     *
     * @param TourStep|null $step
     */
    private function goToStep(?TourStep $step)
    {
        $this->tour->step()->associate($step?->id());
        $this->tour->save();
        $this->tour->refresh();

        TourStepUpdated::dispatch($this, $step, $this->current());
    }

    /**
     * Find a tour by it's ID or name.
     *
     * @param int|string $id ID or name of the tour.
     * @param Tourable $owner If the tour is ownable, the owner to filter by.
     *
     * @return null|Tour
     */
    public static final function find(int|string $id, $owner = null): ?Tour
    {
        /** @var \Illuminate\Database\Eloquent\Builder<StoredTour> $query */
        $query = match (true) {
            is_int($id) => StoredTour::where('id', $id),
            is_string($id) => StoredTour::where('name', $id),
        };

        if ($owner && !in_array(Tourable::class, class_uses_recursive($owner))) {
            throw new \InvalidArgumentException(
                "Cannot get owned tour: expected owner with trait Tourable, got {get_class($owner)}."
            );
        }

        if ($owner) {
            $query = $query
                ->where('owner_id', $owner->id)
                ->where('owner_type', $owner->getMorphClass());
        }

        if (!($model = $query->first())) {
            return null;
        }

        return static::createFromStored($model);
    }

    /**
     * Make an owned tour instance from the given tour name, owned by the given owner.
     *
     * @param string|class-string<Tour&HasOwner>|Tour&HasOwner $name Name or class-name of the tour to create an instance of.
     * @param Tourable $owner The owner of the tour.
     * @throws \InvalidArgumentException
     *
     * @return Tour
     */
    public static final function make(mixed $name, $owner): Tour
    {
        $tour = Tour::createFrom($name);
        $tourName = getObjectIdentifier($tour);

        if (!is_string($name) && !in_array(HasOwner::class, class_uses_recursive($tour))) {
            throw new \InvalidArgumentException(
                "Cannot make owned tour: expected tour with trait HasOwner, got '{$tourName}'."
            );
        }

        if (!in_array(Tourable::class, class_uses_recursive($owner))) {
            throw new \InvalidArgumentException(
                "Cannot make owned tour: expected owner with trait Tourable, got {get_class($owner)}."
            );
        }

        $tour->tour->owner()->associate($owner);
        $tour->tour->save();

        $tour->compiledSteps = $tour->compileSteps();
        $tour->tour->refresh();

        TourStatusUpdated::dispatch($tour, $tour->status(), TourStatus::Created);

        return $tour;
    }

    /**
     * Make an unowned tour instance from the given tour name.
     *
     * @param string|class-string<Tour&HasOwner>|Tour&HasOwner $name Name or class-name of the tour to create an instance of.
     * @throws \InvalidArgumentException
     *
     * @return Tour
     */
    public static final function makeUnowned(mixed $name): Tour
    {
        /** @var Tour */
        $tour = Tour::createFrom($name);

        if (in_array(HasOwner::class, class_uses_recursive(get_class($tour)))) {
            throw new \InvalidArgumentException(
                "Cannot make unowned tour: expected tour without trait HasOwner, got {get_class($tour)}."
            );
        }

        $tour->tour->save();

        $tour->compiledSteps = $tour->compileSteps();
        $tour->tour->refresh();

        TourStatusUpdated::dispatch($tour, $tour->status(), TourStatus::Created);

        return $tour;
    }

    /**
     * Make a tour instance from the given tour class-name or tour instance.
     *
     * @param string|class-string<Tour>|Tour $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Tour
     */
    private static function createFrom($name): Tour
    {
        if (!$name) {
            throw new \InvalidArgumentException("Invalid tour name: null");
        }

        $tourName = getObjectIdentifier($name);

        $tour = match (true) {
            is_string($name) => static::createFromName($name),
            $name instanceof Tour => $name,
            default => throw new \InvalidArgumentException(
                "Cannot make tour: expected string, class-name of instance of Tour or Tour, got {$tourName}."
            )
        };

        if (!$tour || !($tour instanceof Tour)) {
            throw new \InvalidArgumentException(
                "Cannot make tour: expected class-name of instance of Tour, got {$tourName}."
            );
        }

        if (!isset($tour->name) || Str::length($tour->name) == 0) {
            throw new \InvalidArgumentException(
                "Cannot make tour: failed to read 'name' from Tour: {$tourName}"
            );
        }

        $tour->tour = new StoredTour(['name' => $tour->name]);

        return $tour;
    }

    /**
     * Make a tour instance from the given tour class-name or tour name.
     *
     * @param string|class-string<Tour> $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Tour
     */
    private static function createFromName(string $name): Tour
    {
        if (Str::length($name) == 0) {
            throw new \InvalidArgumentException("Cannot make tour: invalid tour name '{$name}'");
        }

        $tourClassName = getModelSubclass($name, Tour::class);

        /** @var Tour */
        $tour = App::make($tourClassName);
        $tour->name ??= $name;

        return $tour;
    }

    /**
     * Make a tour instance from the given stored model.
     *
     * @param StoredTour $model
     *
     * @return Tour
     */
    private static function createFromStored(StoredTour $model): Tour
    {
        /** @var Tour */
        $tour = App::make(Tour::class);
        $tour->tour = $model;
        $tour->name = $model->name;
        $tour->steps = $model->steps()->get()->flatten()->all();
        $tour->compiledSteps = $tour->compileSteps();

        return $tour;
    }
}
