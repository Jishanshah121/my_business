<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Services\Auth\EmailVerificationService;
use App\Services\Auth\Gate;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;

final class EmailVerificationController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly EmailVerificationService $verification,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function notice(): Response
    {
        $user = $this->auth->user();

        if ($user !== null && $user->hasVerifiedEmail()) {
            return $this->redirect('/account');
        }

        return $this->render('auth/verify-email', ['title' => 'Confirm your email']);
    }

    /** Public: the link arrives from an email, so no session is assumed. */
    public function verify(string $token): Response
    {
        [$ok, $message] = $this->verification->verify($token);

        if ($ok) {
            $this->success($message);

            return $this->redirect($this->auth->check() ? '/account' : '/login');
        }

        $this->error($message);

        return $this->redirect($this->auth->check() ? '/email/verify' : '/login');
    }

    public function resend(): Response
    {
        $user = $this->auth->user();

        if ($user === null || $user->hasVerifiedEmail()) {
            return $this->redirectAfterPost('/account');
        }

        // Deliberately the same message whether or not the throttle allowed
        // the send — otherwise this becomes a way to probe rate-limit state.
        $this->verification->send($user);
        $this->success('If your address still needs confirming, a new link is on its way to ' . $user->email . '.');

        return $this->redirectAfterPost('/email/verify');
    }
}
