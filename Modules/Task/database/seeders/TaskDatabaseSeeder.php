<?php

namespace Modules\Task\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Task\Models\Task;

class TaskDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        /*
         * Tasks Seed
         * ------------------
         */

        // DB::table('tasks')->truncate();
        // echo "Truncate: tasks \n";

        Task::factory()->count(20)->create();
        $rows = Task::all();
        echo " Insert: tasks \n\n";

        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
