<?php namespace Orchestra\Foundation\Processor\Account\TestCase;

use Mockery as m;
use Illuminate\Support\Facades\Auth;
use Orchestra\Support\Facades\Foundation;
use Orchestra\Foundation\Testing\TestCase;
use Illuminate\Contracts\Auth\PasswordBroker as Password;
use Orchestra\Foundation\Processor\Account\PasswordBroker;

class PasswordBrokerTest extends TestCase
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
     * Test Orchestra\Foundation\Processor\Account\PasswordBroker::store()
     * method.
     *
     * @test
     */
    public function testStoreMethod()
    {
        $listener  = m::mock('\Orchestra\Contracts\Auth\Listener\PasswordResetLink');
        $validator = m::mock('\Orchestra\Foundation\Validation\AuthenticateUser');
        $resolver  = m::mock('\Illuminate\Contracts\Validation\Validator');
        $password  = m::mock('\Illuminate\Contracts\Auth\PasswordBroker');
        $memory    = m::mock('\Orchestra\Contracts\Memory\Provider');
        $message   = m::mock('\Illuminate\Mailer\Message');

        $input = $this->getStoreInput();

        $stub = new PasswordBroker($validator, $password);

        $validator->shouldReceive('with')->once()->with($input)->andReturn($resolver);
        $resolver->shouldReceive('fails')->once()->andReturn(false);
        $memory->shouldReceive('get')->once()->with('site.name', 'Orchestra Platform')->andReturn('Orchestra Platform');
        $message->shouldReceive('subject')->once()->with(m::type('String'))->andReturnNull();
        $password->shouldReceive('sendResetLink')->once()
            ->with(['email' => $input['email']], m::type('Closure'))
            ->andReturnUsing(function ($d, $c) use ($message) {
                $c($message);

                return Password::RESET_LINK_SENT;
            });
        $listener->shouldReceive('resetLinkSent')->once()->with(Password::RESET_LINK_SENT)->andReturn('reset.sent');

        Foundation::shouldReceive('memory')->once()->andReturn($memory);

        $this->assertEquals('reset.sent', $stub->store($listener, $input));
    }

    /**
     * Test Orchestra\Foundation\Processor\Account\PasswordBroker::store()
     * method given invalid user.
     *
     * @test
     */
    public function testStoreMethodGivenInvalidUser()
    {
        $listener  = m::mock('\Orchestra\Contracts\Auth\Listener\PasswordResetLink');
        $validator = m::mock('\Orchestra\Foundation\Validation\AuthenticateUser');
        $resolver  = m::mock('\Illuminate\Contracts\Validation\Validator');
        $password  = m::mock('\Illuminate\Contracts\Auth\PasswordBroker');
        $memory    = m::mock('\Orchestra\Contracts\Memory\Provider');

        $input = $this->getStoreInput();

        $stub = new PasswordBroker($validator, $password);

        $validator->shouldReceive('with')->once()->with($input)->andReturn($resolver);
        $resolver->shouldReceive('fails')->once()->andReturn(false);
        $memory->shouldReceive('get')->once()->with('site.name', 'Orchestra Platform')->andReturn('Orchestra Platform');
        $password->shouldReceive('sendResetLink')->once()
            ->with(['email' => $input['email']], m::type('Closure'))
            ->andReturnUsing(function ($d, $c) {
                return Password::INVALID_USER;
            });
        $listener->shouldReceive('resetLinkFailed')->once()->with(Password::INVALID_USER)->andReturn('reset.not.sent');

        Foundation::shouldReceive('memory')->once()->andReturn($memory);

        $this->assertEquals('reset.not.sent', $stub->store($listener, $input));
    }

    /**
     * Test Orchestra\Foundation\Processor\Account\PasswordBroker::store()
     * method given failed validation.
     *
     * @test
     */
    public function testStoreMethodGivenFailedValidation()
    {
        $listener  = m::mock('\Orchestra\Contracts\Auth\Listener\PasswordResetLink');
        $validator = m::mock('\Orchestra\Foundation\Validation\AuthenticateUser');
        $resolver  = m::mock('\Illuminate\Contracts\Validation\Validator');
        $password  = m::mock('\Illuminate\Contracts\Auth\PasswordBroker');

        $input = $this->getStoreInput();

        $stub = new PasswordBroker($validator, $password);

        $validator->shouldReceive('with')->once()->with($input)->andReturn($resolver);
        $resolver->shouldReceive('fails')->once()->andReturn(true)
            ->shouldReceive('getMessageBag')->once()->andReturn([]);
        $listener->shouldReceive('resetLinkFailedValidation')->once()->with([])->andReturn('reset.failed.validation');

        $this->assertEquals('reset.failed.validation', $stub->store($listener, $input));
    }

    /**
     * Test Orchestra\Foundation\Processor\Account\PasswordBroker::update()
     * method.
     *
     * @test
     */
    public function testUpdateMethod()
    {
        $listener  = m::mock('\Orchestra\Contracts\Auth\Listener\PasswordReset');
        $validator = m::mock('\Orchestra\Foundation\Validation\AuthenticateUser');
        $password  = m::mock('\Illuminate\Contracts\Auth\PasswordBroker');
        $user      = m::mock('\Orchestra\Model\User');

        $input = $this->getUpdateInput();

        $stub = new PasswordBroker($validator, $password);

        $user->shouldReceive('setAttribute')->once()->with('password', $input['password'])->andReturnNull()
            ->shouldReceive('save')->once()->andReturnNull();
        $password->shouldReceive('reset')->once()
            ->with($input, m::type('Closure'))
            ->andReturnUsing(function ($d, $c) use ($user, $input) {
                $c($user, $input['password']);

                return Password::PASSWORD_RESET;
            });
        $listener->shouldReceive('passwordHasReset')->once()->with(Password::PASSWORD_RESET)->andReturn('reset.done');

        Auth::shouldReceive('login')->once()->with($user)->andReturnNull();

        $this->assertEquals('reset.done', $stub->update($listener, $input));
    }

    /**
     * Test Orchestra\Foundation\Processor\Account\PasswordBroker::store()
     * method given failed execution.
     *
     * @test
     */
    public function testUpdateMethodGivenFailed()
    {
        $listener  = m::mock('\Orchestra\Contracts\Auth\Listener\PasswordReset');
        $validator = m::mock('\Orchestra\Foundation\Validation\AuthenticateUser');
        $password  = m::mock('\Illuminate\Contracts\Auth\PasswordBroker');

        $input = $this->getUpdateInput();

        $stub = new PasswordBroker($validator, $password);

        $password->shouldReceive('reset')->once()
            ->with($input, m::type('Closure'))
            ->andReturnUsing(function ($d, $c) {
                return Password::INVALID_PASSWORD;
            });
        $listener->shouldReceive('passwordResetHasFailed')->once()->with(Password::INVALID_PASSWORD)->andReturn('reset.failed');

        $this->assertEquals('reset.failed', $stub->update($listener, $input));
    }

    /**
     * Get request input for store.
     *
     * @return array
     */
    protected function getStoreInput()
    {
        return [
            'email' => 'email@orchestraplatform.com',
        ];
    }

    /**
     * Get request input for update.
     *
     * @return array
     */
    protected function getUpdateInput()
    {
        return [
            'email' => 'email@orchestraplatform.com',
            'password' => '123456',
            'password_confirmation' => '123456',
            'token' => 'auniquetoken',
        ];
    }
}
