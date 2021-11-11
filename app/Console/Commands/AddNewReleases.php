<?php

namespace App\Console\Commands;

use App\Services\Tasks;
use App\Traits\ConsoleReport;
use Illuminate\Console\Command;

class AddNewReleases extends Command
{
    use ConsoleReport;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:add-new-releases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new releases ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Adding...');
        $startTime = time();
        $report = (new Tasks())->addNewReleases();
        $endTime = time();
        $duration = $endTime - $startTime;
        $this->info('Success: New releases added');
        $this->info('Time: ' . $duration . ' seconds');
        $this->showReport($report->getReport());
    }
}
