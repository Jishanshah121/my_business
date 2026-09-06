<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Services\Auth\PasswordResetService;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;
use App\Validators\Validator;

final class PasswordResetController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly PasswordResetService $resets,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function showRequestForm(): Response
    {
        return $this->render('auth/forgot-password', ['title' => 'Reset your password']);
    }

    public function sendLink(Request $request): Response
    {
        $data = Validator::make(
            $request->only(['email']),
            ['email' => 'required|email|max:191'],
            ['email' => 'Email address']
        )->validate();

        [$allowed, $message] = $this->resets->requestLink($request, (string) $data['email']);

        if (!$allowed) {
            $this->error($message);

            return $this->redirectAfterPost('/forgot-password');
        }

        // The same confirmation regardless of whether the address exists.
        $this->success($message);

        return $this->redirectAfterPost('/forgot-password');
    }

    public function showResetForm(string $token): Response
    {
        if ($this->resets->findValidToken($token) === null) {
            $this->error('That reset link has expired or has already been used. Request a new one.');

            return $this->redirect('/forgot-password');
        }

        return $this->render('auth/reset-password', [
            'title' => 'Choose a new password',
            'token' => $token,
        ]);
    }

    public function reset(Request $request, string $token): Response
    {
        $data = Validator::make(
            $request->only(['password', 'password_confirmation']),
            ['password' => 'required|password|confirmed|max:200'],
            ['password' => 'Password']
        )->validate();

        [$ok, $message] = $this->resets->reset($token, (string) $data['password']);

        if (!$ok) {
            $this->error($message);

            return $this->redirectAfterPost('/forgot-password');
        }

        $this->success($message);

        return $this->redirectAfterPost('/login');
    }
}
