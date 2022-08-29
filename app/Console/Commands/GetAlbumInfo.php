<?php

namespace App\Console\Commands;

use App\Facades\Spotify;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GetAlbumInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-album-info {album_id} {field?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get album info from Spotify';

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
        $albumId = $this->argument('album_id');
        $field = $this->argument('field') ?? null;

        if (!isset($albumId)) {
            $this->error('Album id required');
            return;
        }

        if (Cache::has('client_access_token')) {
            $accessToken = Cache::get('client_access_token');
        } else {
            $accessToken = Spotify::getClientAccessToken();
        }

        $fullAlbum = Spotify::getAlbum($accessToken, $albumId);

        if (isset($field)) {
            $this->info(print_r($fullAlbum->$field, true));
        } else {
            $this->info(print_r($fullAlbum, true));
        }
    }
}
