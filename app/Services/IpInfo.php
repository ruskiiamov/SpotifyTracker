<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IpInfo
{
    const URL_TEMPLATE = 'https://ipinfo.io/%s/json';

    /**
     * @param string $ip
     * @return string|null
     */
    public function getCountryCode(string $ip): ?string
    {
        $url = sprintf(self::URL_TEMPLATE, $ip);

        for ($i = 0; $i < 3; $i++) {
            try {
                $response = Http::get($url)->json();
                break;
            } catch (\Exception $e) {
                sleep(1);
            }
        }

        return $response['country'] ?? null;
    }
}
