<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Tracker;
use Illuminate\Console\Command;

class GenresAnalyse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:genres-analyse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyse most repeated words in genre titles';

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
     * @param Tracker $tracker
     * @return void
     */
    public function handle(Tracker $tracker)
    {
        $words = $tracker->genresAnalyse();
        $this->table(['Word', 'Frequency'], $words);
    }
}
