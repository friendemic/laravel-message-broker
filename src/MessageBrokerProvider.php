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
    public function boot(): void
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
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/message_broker.php', 'message_broker');

        $this->app->singleton('message_broker', function ($app) {
            return new MessageBrokerManager($app);
        });
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return ['message_broker'];
    }
}
