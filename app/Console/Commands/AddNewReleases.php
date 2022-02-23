<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\AddNewReleases as AddNewReleasesJob;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AddNewReleases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:queue-add-new-releases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new releases ';

    /**
     * Markets for new releases searching
     *
     * @var array
     */
    private array $markets;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->markets = config('spotifyConfig.markets');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->markets as $market) {
            try {
                AddNewReleasesJob::dispatch('new', $market);//TODO change to enum
                AddNewReleasesJob::dispatch('hipster', $market);
            } catch (Exception $e) {
                Log::error($e->getMessage(), ['method' => __METHOD__]);
            }
        }
    }
}
