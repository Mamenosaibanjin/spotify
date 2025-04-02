<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Spotify\SpotifyExtendSocialite;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SocialiteWasCalled::class => [
            SpotifyExtendSocialite::class.'@handle',
        ],
    ];
}
