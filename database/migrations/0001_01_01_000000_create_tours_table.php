<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Pointer\Models\StoredTourStep;
use Pointer\TourStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('pointer.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/pointer.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['tours'], function (Blueprint $table) {
            $table->id();
            $table->text('name')->index();
            $table->nullableMorphs('owner');
            $table->enum('status', TourStatus::values());
            $table->json('context')->nullable();
            $table->foreignIdFor(StoredTourStep::class, 'step')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create($tableNames['tour_steps'], function (Blueprint $table) use ($tableNames) {
            $table->id();
            $table->text('name')->index();
            $table->foreignId('tour_id')
                ->constrained(table: $tableNames['tours'])
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
