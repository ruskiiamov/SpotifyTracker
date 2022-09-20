<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheTtlTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cache-ttl-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test real ttl for redis cache';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $key = 'ttl-test';

        if (!Cache::has($key)) {
            Log::info('TTL TEST: <<< KEY NOT FOUND >>>');
            Cache::put($key, 1, config('spotifyConfig.cache_ttl'));
            Log::info('TTL TEST: ttl=' . config('spotifyConfig.cache_ttl'));
        } else {
            Log::info('TTL TEST: key exists');
        }

    }
}
