<?php

namespace Modules\Task\database\factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Task\Models\Task::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph,
            'status' => 'pending',
            'due_at' => Carbon::now()->addDays(rand(1, 14)),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
