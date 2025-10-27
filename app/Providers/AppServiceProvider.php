<?php

namespace App\Providers;

use App\Listeners\NewMicrosoft365SignInListener;
use Dcblogdev\MsGraph\Events\NewMicrosoft365SignInEvent;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /* Scramble::configure()
         ->withDocumentTransformers(function (OpenApi $openApi) {
             $openApi->secure(
                 SecurityScheme::http('bearer')
             );
         });*/
        Model::unguard();
        Event::listen(
            NewMicrosoft365SignInEvent::class,
            [NewMicrosoft365SignInListener::class, 'handle']
        );
    }
}
