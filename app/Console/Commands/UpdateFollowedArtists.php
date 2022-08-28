<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Facades\Spotify;
use App\Jobs\UpdateUserFollowedArtists;
use App\Models\User;
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
    protected $signature = 'app:queue-update-followed-artists';

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
     * @return void
     */
    public function handle()
    {
        if (Spotify::areRequestsAvailable()) {
            User::chunk(200, function ($users) {
                foreach ($users as $user) {
                    try {
                        UpdateUserFollowedArtists::dispatch($user);
                    } catch (Exception $e) {
                        Log::error($e->getMessage(), ['method' => __METHOD__, 'user_id' => $user->id]);
                    }
                }
            });
        }
    }
}
