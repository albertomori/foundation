<?php namespace Orchestra\Foundation\Routing;

abstract class AdminController extends BaseController
{
    /**
     * Base construct method.
     */
    public function __construct()
    {
        // Admin controllers should be accessible only after
        // Orchestra Platform is installed.
        $this->beforeFilter('orchestra.installable');

        parent::__construct();
    }
}
