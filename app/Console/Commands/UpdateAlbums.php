<?php

namespace App\Console\Commands;

use App\Services\Tracker;
use App\Traits\ConsoleReport;
use Illuminate\Console\Command;

class UpdateAlbums extends Command
{
    use ConsoleReport;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:update-albums';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update albums popularity abd delete obsolete albums';

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
        $this->line('Updating...');
        $startTime = time();
        $report = (new Tracker())->updateAlbums();
        $endTime = time();
        $duration = $endTime - $startTime;
        $this->info('Success: Albums table updated');
        $this->info('Time: ' . $duration . ' seconds');
        $this->showReport($report->getReport());
    }
}
