<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Tracker;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateFollowedArtists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spotify:update-followed-artists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update followed artists for all users';

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
        User::chunk(200, function ($users) use ($tracker) {
            foreach ($users as $user) {
                try {
                    $tracker->updateUserFollowedArtists($user); //TODO change to Job Detaching
                } catch (Exception $e) {
                    Log::error($e->getMessage(), ['method' => __METHOD__, 'user_id' => $user->id]);
                }
            }
        });
    }
}
