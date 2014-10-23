<?php namespace Orchestra\Foundation\Routing;

use Orchestra\Support\Facades\Meta;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Orchestra\Foundation\Processor\Dashboard as DashboardProcessor;

class DashboardController extends AdminController
{
    /**
     * Dashboard controller routing.
     *
     * @param \Orchestra\Foundation\Processor\Dashboard    $processor
     */
    public function __construct(DashboardProcessor $processor)
    {
        $this->processor = $processor;

        parent::__construct();
    }

    /**
     * Setup controller filters.
     *
     * @return void
     */
    protected function setupFilters()
    {
        // User has to be authenticated before using this controller.
        $this->beforeFilter('orchestra.auth', array(
            'only' => array('index'),
        ));
    }

    /**
     * Show User Dashboard.
     *
     * GET (:orchestra)/
     *
     * @return mixed
     */
    public function index()
    {
        Meta::set('title', trans("orchestra/foundation::title.home"));

        return $this->processor->show($this);
    }

    /**
     * Show missing pages.
     *
     * GET (:orchestra) return 404
     *
     * @return mixed
     */
    public function missing()
    {
        throw new NotFoundHttpException("Controller method not found.");
    }

    /**
     * Response with widget.
     *
     * @param  array  $data
     * @return mixed
     */
    public function dashboardSucceed(array $data)
    {
        return View::make('orchestra/foundation::dashboard.index', $data);
    }
}
