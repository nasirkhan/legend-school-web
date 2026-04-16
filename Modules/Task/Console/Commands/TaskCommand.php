<?php

namespace Modules\Task\Console\Commands;

use Illuminate\Console\Command;

class TaskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:TaskCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Task Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
