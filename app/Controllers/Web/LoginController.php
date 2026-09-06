<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;
use App\Validators\Validator;

final class LoginController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function show(Request $request): Response
    {
        $redirect = $request->query('redirect');
        if (is_string($redirect) && $this->isSafeRedirect($redirect)) {
            $this->session->put('_intended_url', $redirect);
        }

        return $this->render('auth/login', ['title' => 'Sign in']);
    }

    public function store(Request $request): Response
    {
        $data = Validator::make(
            $request->only(['identifier', 'password']),
            [
                'identifier' => 'required|max:191',
                'password'   => 'required|max:200',
            ],
            ['identifier' => 'Email or mobile number', 'password' => 'Password']
        )->validate();

        [$ok, $message] = $this->auth->attempt($request, (string) $data['identifier'], (string) $data['password']);

        if (!$ok) {
            $this->withErrors(['identifier' => [(string) $message]], ['identifier' => $data['identifier']]);

            return $this->redirectAfterPost('/login');
        }

        $user = $this->auth->user();
        $this->success('Welcome back, ' . ($user?->firstName ?? 'there') . '.');

        // Staff land in the admin area, customers in their account.
        $default = $user !== null && $user->isStaff() ? '/admin' : '/account';

        return $this->redirectAfterPost($this->intendedUrl($default));
    }

    public function destroy(): Response
    {
        $this->auth->logout();
        $this->success('You have been signed out.');

        return $this->redirectAfterPost('/');
    }
}
