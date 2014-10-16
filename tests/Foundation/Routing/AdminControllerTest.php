<?php namespace Orchestra\Foundation\Routing\TestCase;

use Mockery as m;
use Illuminate\Support\Facades\Event;
use Orchestra\Foundation\Testing\TestCase;

class AdminControllerTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }

    /**
     * Test Orchestra\Foundation\Routing\AdminController filters.
     */
    public function testFilters()
    {
        Event::swap($event = m::mock('\Illuminate\Events\Dispatcher[fire]'));

        $event->shouldReceive('fire')->once()->with('orchestra.started: admin')->andReturnNull()
            ->shouldReceive('fire')->once()->with('orchestra.ready: admin')->andReturnNull()
            ->shouldReceive('fire')->once()->with('orchestra.done: admin')->andReturnNull();

        StubAdminController::setRouter($route = m::mock('Illuminate\Routing\Router'));

        $route->shouldReceive('filter')->twice()
            ->with(m::type('String'), m::type('Closure'))->andReturnUsing(function ($name, $callback) {
                $callback();
                return $name;
            });

        new StubAdminController;
    }
}

class StubAdminController extends \Orchestra\Foundation\Routing\AdminController
{
    protected function setupFilters()
    {
        //
    }
}
