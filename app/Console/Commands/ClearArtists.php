<?php

namespace App\Console\Commands;

use App\Services\Tasks;
use App\Traits\ConsoleReport;
use Illuminate\Console\Command;

class ClearArtists extends Command
{
    use ConsoleReport;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:clear-artists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete artists without followers';

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
     */
    public function handle()
    {
        $this->line('Clearing...');
        $startTime = time();
        $report = (new Tasks())->clearArtists();
        $endTime = time();
        $duration = $endTime - $startTime;
        $this->info('Success: Artists table cleared');
        $this->info('Time: ' . $duration . ' seconds');
        $this->showReport($report->getReport());
    }
}
