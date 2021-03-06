<?php namespace Orchestra\Foundation\Presenter\TestCase;

use Mockery as m;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Fluent;
use Orchestra\Foundation\Presenter\Setting;

class SettingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->app = new Container;

        $this->app['orchestra.app'] = m::mock('\Orchestra\Foundation\Foundation')->makePartial();
        $this->app['translator'] = m::mock('\Illuminate\Translation\Translator')->makePartial();

        $this->app['orchestra.app']->shouldReceive('handles');
        $this->app['translator']->shouldReceive('trans');

        Facade::clearResolvedInstances();
        Container::setInstance($this->app);
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        unset($this->app);

        m::close();
    }

    /**
     * Test Orchestra\Foundation\Presenter\Setting::form()
     * method.
     *
     * @test
     */
    public function testFormMethod()
    {
        $app   = $this->app;
        $model = new Fluent(array(
            'email_password' => 123456,
        ));

        $app['Illuminate\Contracts\View\Factory'] = m::mock('\Illuminate\View\Factory');

        $form = m::mock('\Orchestra\Contracts\Html\Form\Factory');
        $grid = m::mock('\Orchestra\Contracts\Html\Form\Grid');

        $siteFieldset = m::mock('\Orchestra\Contracts\Html\Form\Fieldset');
        $siteControl  = m::mock('\Orchestra\Contracts\Html\Form\Control');

        $emailFieldset = m::mock('\Orchestra\Contracts\Html\Form\Fieldset');
        $emailControl  = m::mock('\Orchestra\Contracts\Html\Form\Control');

        $stub = new Setting($form);

        $siteFieldset->shouldReceive('control')->times(3)->andReturn($siteControl);
        $siteControl->shouldReceive('label')->times(3)->andReturnSelf()
            ->shouldReceive('attributes')->twice()->andReturnSelf()
            ->shouldReceive('options')->once()->andReturnSelf();

        $emailFieldset->shouldReceive('control')->times(11)
                ->with(m::any(), m::any())->andReturn($emailControl);
        $emailControl->shouldReceive('label')->times(11)->andReturnSelf()
            ->shouldReceive('attributes')->once()->andReturnSelf()
            ->shouldReceive('options')->twice()->andReturnSelf()
            ->shouldReceive('help')->once()->with('email.password.help');

        $grid->shouldReceive('setup')->once()
                ->with($stub, 'orchestra::settings', $model)->andReturnNull()
            ->shouldReceive('fieldset')->once()
                ->with(trans('orchestra/foundation::label.settings.application'), m::type('Closure'))
                ->andReturnUsing(function ($t, $c) use ($siteFieldset) {
                    $c($siteFieldset);
                })
            ->shouldReceive('fieldset')->once()
                ->with(trans('orchestra/foundation::label.settings.mail'), m::type('Closure'))
                ->andReturnUsing(function ($t, $c) use ($emailFieldset) {
                    $c($emailFieldset);
                });

        $form->shouldReceive('of')->once()
                ->with('orchestra.settings', m::type('Closure'))
                ->andReturnUsing(function ($n, $c) use ($grid) {
                    $c($grid);
                    return 'foo';
                });

        $app['Illuminate\Contracts\View\Factory']->shouldReceive('make')->once()
            ->with('orchestra/foundation::settings.email-password', compact('model'), [])
            ->andReturn('email.password.help');

        $this->assertEquals('foo', $stub->form($model));
    }
}
