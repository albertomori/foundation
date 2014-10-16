<?php namespace Orchestra\Foundation\Processor;

use Exception;
use Orchestra\Model\User;
use Orchestra\Support\Str;
use Orchestra\Notifier\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Orchestra\Support\Facades\Notifier;
use Orchestra\Support\Facades\Foundation;
use Illuminate\Contracts\Support\Arrayable;
use Orchestra\Foundation\Presenter\Account as AccountPresenter;
use Orchestra\Foundation\Validation\Account as AccountValidator;

class Registration extends AbstractableProcessor
{
    /**
     * Create a new processor instance.
     *
     * @param  \Orchestra\Foundation\Presenter\Account  $presenter
     * @param  \Orchestra\Foundation\Validation\Account $validator
     */
    public function __construct(AccountPresenter $presenter, AccountValidator $validator)
    {
        $this->presenter = $presenter;
        $this->validator = $validator;
    }

    /**
     * View registration page.
     *
     * @param  object  $listener
     * @return mixed
     */
    public function index($listener)
    {
        $eloquent = Foundation::make('orchestra.user');

        $title = 'orchestra/foundation::title.register';
        $form  = $this->presenter->profile($eloquent, 'orchestra::register');

        $form->extend(function ($form) use ($title) {
            $form->submit = $title;
        });

        Event::fire('orchestra.form: user.account', array($eloquent, $form));

        return $listener->indexSucceed(compact('eloquent', 'form'));
    }

    /**
     * Create a new user.
     *
     * @param  object  $listener
     * @param  array   $input
     * @return mixed
     */
    public function create($listener, array $input)
    {
        $password = Str::random(5);

        $validation = $this->validator->on('register')->with($input);

        // Validate user registration, if any errors is found redirect it
        // back to registration page with the errors
        if ($validation->fails()) {
            return $listener->createValidationFailed($validation);
        }

        $user = Foundation::make('orchestra.user');

        $user->email    = $input['email'];
        $user->fullname = $input['fullname'];
        $user->password = $password;

        try {
            $this->fireEvent('creating', array($user));
            $this->fireEvent('saving', array($user));

            DB::transaction(function () use ($user) {
                $user->save();
                $user->roles()->sync(array(
                    Config::get('orchestra/foundation::roles.member', 2)
                ));
            });

            $this->fireEvent('created', array($user));
            $this->fireEvent('saved', array($user));
        } catch (Exception $e) {
            return $listener->createFailed(array('error' => $e->getMessage()));
        }

        return $this->sendEmail($listener, $user, $password);
    }

    /**
     * Send new registration e-mail to user.
     *
     * @param  object                  $listener
     * @param  \Orchestra\Model\User   $user
     * @param  string                  $password
     * @return mixed
     */
    protected function sendEmail($listener, User $user, $password)
    {
        // Converting the user to an object allow the data to be a generic
        // object. This allow the data to be transferred to JSON if the
        // mail is send using queue.

        $memory = Foundation::memory();
        $site   = $memory->get('site.name', 'Orchestra Platform');

        $data = array(
            'password' => $password,
            'site'     => $site,
            'user'     => ($user instanceof Arrayable ? $user->toArray() : $user),
        );

        $subject = trans('orchestra/foundation::email.credential.register', array('site' => $site));
        $view    = 'emails.auth.register';
        $message = Message::create($view, $data, $subject);

        $receipt = Notifier::send($user, $message);

        if ($receipt->failed()) {
            return $listener->createSucceedWithoutNotification();
        }

        return $listener->createSucceed();
    }

    /**
     * Fire Event related to eloquent process
     *
     * @param  string  $type
     * @param  array   $parameters
     * @return void
     */
    protected function fireEvent($type, array $parameters = array())
    {
        Event::fire("orchestra.{$type}: user.account", $parameters);
    }
}
