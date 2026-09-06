<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\JsonResponse;
use App\Http\RedirectResponse;
use App\Http\Response;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;

/**
 * Base controller. Provides response helpers and shares the handful of things
 * every view needs. Controllers translate HTTP into service calls and back —
 * they hold no business rules and no SQL.
 */
abstract class Controller
{
    public function __construct(
        protected readonly View $view,
        protected readonly Session $session,
        protected readonly AuthService $auth,
        protected readonly Gate $gate,
        protected readonly Csrf $csrf,
    ) {
    }

    /** @param array<string,mixed> $data */
    protected function render(string $template, array $data = [], int $status = 200): Response
    {
        $this->view->shareMany([
            'view'      => $this->view,
            'auth'      => $this->auth,
            'gate'      => $this->gate,
            'csrf'      => $this->csrf,
            'user'      => $this->auth->user(),
            'errors'    => (array) $this->session->getFlash('errors', []),
            'old'       => (array) $this->session->getFlash('old', []),
            'flash'     => [
                'success' => $this->session->getFlash('success'),
                'error'   => $this->session->getFlash('error'),
                'warning' => $this->session->getFlash('warning'),
                'info'    => $this->session->getFlash('info'),
            ],
        ]);

        return new Response(
            $this->view->render($template, $data),
            $status,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    protected function redirect(string $to, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($to, $status);
    }

    /**
     * Redirect after a successful POST.
     *
     * 303 rather than 302 so the browser switches to GET and a refresh cannot
     * resubmit the form.
     */
    protected function redirectAfterPost(string $to): RedirectResponse
    {
        return new RedirectResponse($to, 303);
    }

    /** Return to where the user came from, staying on this site. */
    protected function back(string $fallback = '/'): RedirectResponse
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        // Only follow a relative path or a same-host URL — an open redirect
        // here would let a phishing page bounce through our domain.
        if (is_string($referer) && $this->isSafeRedirect($referer)) {
            return new RedirectResponse($referer, 303);
        }

        return new RedirectResponse($fallback, 303);
    }

    protected function isSafeRedirect(string $url): bool
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url((string) \App\Support\Config::get('app.url'), PHP_URL_HOST);

        return is_string($host) && is_string($appHost) && strcasecmp($host, $appHost) === 0;
    }

    /** Where to send a user after signing in. */
    protected function intendedUrl(string $default = '/account'): string
    {
        $intended = $this->session->pull('_intended_url');

        return is_string($intended) && $this->isSafeRedirect($intended) ? $intended : $default;
    }

    /** @param array<string,mixed> $data */
    protected function json(array $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }

    protected function success(string $message): void
    {
        $this->session->flash('success', $message);
    }

    protected function error(string $message): void
    {
        $this->session->flash('error', $message);
    }

    protected function info(string $message): void
    {
        $this->session->flash('info', $message);
    }

    /**
     * Re-show a form with its messages and previous input.
     *
     * @param array<string,list<string>> $errors
     * @param array<string,mixed> $old
     */
    protected function withErrors(array $errors, array $old = []): void
    {
        $this->session->flash('errors', $errors);
        $this->session->flash('old', $old);
    }
}
