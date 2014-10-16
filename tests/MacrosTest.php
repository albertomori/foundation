<?php namespace Orchestra\TestCase;

use Mockery as m;
use Illuminate\Support\Facades\App;
use Orchestra\Support\Facades\HTML;
use Orchestra\Support\Facades\Site;
use Orchestra\Foundation\Testing\TestCase;

class MacrosTest extends TestCase
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
     * Test HTML::title() macro.
     *
     * @test
     */
    public function testHtmlTitleMacro()
    {
        $this->app['orchestra.platform.memory'] = $memory = m::mock('\Orchestra\Memory\Provider')->makePartial();

        $memory->shouldReceive('get')->once()->with('site.name', '')->andReturn('Foo')
            ->shouldReceive('get')->once()
                ->with('site.format.title', ':pageTitle &mdash; :siteTitle')
                ->andReturn(':pageTitle &mdash; :siteTitle');

        $this->assertEquals('<title>Foo</title>', HTML::title());
    }

    /**
     * Test HTML::title() macro with page title.
     *
     * @test
     */
    public function testHtmlTitleMacroWithPageTitle()
    {
        $this->app['orchestra.platform.memory'] = $memory = m::mock('\Orchestra\Memory\Provider')->makePartial();
        Site::shouldReceive('get')->once()->with('title', '')->andReturn('Foobar');

        $memory->shouldReceive('get')->once()->with('site.name', '')->andReturn('Foo')
            ->shouldReceive('get')->once()->with('site.format.title', ':pageTitle &mdash; :siteTitle')
            ->andReturn(':pageTitle &mdash; :siteTitle');

        $this->assertEquals('<title>Foobar &mdash; Foo</title>', HTML::title());
    }

    /**
     * Test Orchestra\Decorator navbar is registered.
     *
     * @test
     */
    public function testDecoratorIsRegistered()
    {
        $stub = App::make('orchestra.decorator');
        $view = $stub->render('navbar', array());

        $this->assertInstanceOf('\Orchestra\View\Decorator', $stub);
        $this->assertInstanceOf('\Illuminate\View\View', $view);
        $this->assertEquals('orchestra/foundation::components.navbar', $view->getName());
    }
}
