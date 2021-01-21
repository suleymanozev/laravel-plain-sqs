<?php

namespace KeithBrink\PlainSqs;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use KeithBrink\PlainSqs\Sqs\Connector;

/**
 * Class CustomQueueServiceProvider.
 */
class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/sqs-plain.php' => config_path('sqs-plain.php'),
        ]);

        Queue::after(function (JobProcessed $event) {
            try {
                if (! $event->job->isDeletedOrReleased()) {
                    $event->job->delete();
                }
            } catch (\Exception $e) {
                // Ignore...
            }
        });
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->app->booted(function () {
            $this->app['queue']->extend('sqs-plain', function () {
                return new Connector();
            });
        });
    }
}
