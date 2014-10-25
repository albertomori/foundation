<?php namespace Orchestra\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

class SetRequestForConsole
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        if (! $app->bound('request')) {
            $app->setRequestForConsoleEnvironment();
        }
    }
}
