<?php

use Orchestra\Support\Facades\App;

if (! function_exists('orchestra')) {
    /**
     * Return orchestra.app instance.
     *
     * @return \Orchestra\Foundation\Application
     */
    function orchestra()
    {
        return app('orchestra.app');
    }
}

if (! function_exists('memorize')) {
    /**
     * Return memory configuration associated to the request.
     *
     * @param  string   $key
     * @param  string   $default
     * @return mixed
     * @see    \Orchestra\Foundation\Kernel::memory()
     */
    function memorize($key, $default = null)
    {
        return App::memory()->get($key, $default);
    }
}

if (! function_exists('handles')) {
    /**
     * Return handles configuration for a package/app.
     *
     * @param  string   $name   Route
     * @return string
     */
    function handles($name)
    {
        return App::handles($name);
    }
}

if (! function_exists('resources')) {
    /**
     * Return resources route.
     *
     * @param  string   $name   Route
     * @return string
     */
    function resources($name)
    {
        $name = ltrim($name, '/');

        return App::handles("orchestra/foundation::resources/{$name}");
    }
}
