<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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
        $ttl = 2 * 60 * 60;

        if (!Redis::exists($key)) {
            Log::info('TTL TEST: <<< KEY NOT FOUND >>>');
            Redis::set($key, 1, $ttl);
            Log::info('TTL TEST: ttl=' . $ttl);
        } else {
            Log::info('TTL TEST: key exists');
        }

    }
}
