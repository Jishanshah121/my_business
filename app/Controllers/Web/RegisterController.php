<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Services\Auth\RegistrationService;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;
use App\Validators\Validator;

final class RegisterController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly RegistrationService $registration,
        private readonly UserRepository $users,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function show(Request $request): Response
    {
        $redirect = $request->query('redirect');
        if (is_string($redirect) && $this->isSafeRedirect($redirect)) {
            $this->session->put('_intended_url', $redirect);
        }

        return $this->render('auth/register', ['title' => 'Create your account']);
    }

    public function store(Request $request): Response
    {
        $input = $request->only([
            'first_name', 'last_name', 'email', 'phone',
            'password', 'password_confirmation', 'marketing_opt_in', 'terms',
        ]);

        $validator = Validator::make($input, [
            'first_name' => 'required|name|max:96',
            'last_name'  => 'nullable|name|max:96',
            'email'      => 'required|email|max:191',
            'phone'      => 'nullable|phone',
            'password'   => 'required|password|confirmed|max:200',
            'terms'      => 'accepted',
        ], [
            'first_name' => 'First name',
            'last_name'  => 'Last name',
            'email'      => 'Email address',
            'phone'      => 'Mobile number',
            'password'   => 'Password',
            'terms'      => 'terms and conditions',
        ]);

        // Uniqueness is checked here rather than as a rule so the message can
        // point at signing in instead of just saying "taken".
        $validator->after('email', function (mixed $email): ?string {
            return is_string($email) && $email !== '' && $this->users->emailExists($email)
                ? 'An account already exists with that email. Try signing in instead.'
                : null;
        });

        $validator->after('phone', function (mixed $phone): ?string {
            if (!is_string($phone) || $phone === '') {
                return null;
            }

            return $this->users->phoneExists(Validator::normaliseIndianMobile($phone))
                ? 'That mobile number is already registered.'
                : null;
        });

        $data = $validator->validate();
        $data['marketing_opt_in'] = $request->boolean('marketing_opt_in');

        $user = $this->registration->registerCustomer($data);
        $this->auth->login($user);

        $this->success('Your account is ready. We have sent a confirmation link to ' . $user->email . '.');

        return $this->redirectAfterPost($this->intendedUrl('/account'));
    }
}
