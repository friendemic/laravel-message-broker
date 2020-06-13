<?php

namespace Friendemic\MessageBroker;

use Illuminate\Support\ServiceProvider;

class MessageBrokerProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/message_broker.php' => config_path('message_broker.php')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/message_broker.php', 'message_broker');

        $this->app->singleton('message_broker', function ($app) {
            return new MessageBrokerManager($app);
        });
    }

    public function provides()
    {
        return ['message_broker'];
    }
}
