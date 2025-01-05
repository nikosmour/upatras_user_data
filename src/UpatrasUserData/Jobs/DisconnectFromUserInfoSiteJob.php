<?php

namespace UpatrasUserData\Jobs;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;


class DisconnectFromUserInfoSiteJob implements ShouldQueue
{
    use Queueable;
    protected  array $cookies;

    public function __construct($cookies)
    {
        $this->cookies = $cookies;
    }

    // The logic that you want to execute in the background
    public function handle()
    {
        $cookies = $this->cookies;
        $url= 'https://mussa.upnet.gr/index.php?action=logout';
        Http::withOptions(['verify' => true])
            ->withCookies($cookies, 'mussa.upnet.gr') // Include cookies
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]) // Specify form data format
            ->get($url);
    }

}