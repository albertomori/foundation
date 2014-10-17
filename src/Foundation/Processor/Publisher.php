<?php namespace Orchestra\Foundation\Processor;

use Illuminate\Support\Facades\Session;
use Orchestra\Support\Facades\Publisher as P;
use Orchestra\Support\Ftp\ServerException;

class Publisher extends Processor
{
    /**
     * Run publishing if possible.
     *
     * @param  object  $listener
     * @return mixed
     */
    public function index($listener)
    {
        P::connected() && P::execute();

        return $listener->redirectToPublisher();
    }

    /**
     * Publish process.
     *
     * @param  object  $listener
     * @param  array   $input
     * @return mixed
     */
    public function publish($listener, array $input)
    {
        $queues = P::queued();

        // Make an attempt to connect to service first before
        try {
            P::connect($input);
        } catch (ServerException $e) {
            Session::forget('orchestra.ftp');

            return $listener->publishFailed($e->getMessage());
        }

        Session::put('orchestra.ftp', $input);

        if (P::connected() && ! empty($queues)) {
            P::execute();
        }

        return $listener->redirectToPublisher();
    }
}
