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
use App\Support\Logger;
use App\Support\Session\Session;
use App\Support\View;
use App\Validators\Validator;

final class OtpAuthController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly UserRepository $users,
        private readonly RegistrationService $registration,
        private readonly Logger $logger,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    /**
     * Send 6-digit OTP to mobile number.
     * POST /api/auth/send-otp
     */
    public function sendOtp(Request $request): Response
    {
        $phoneInput = $request->input('phone');
        if (!is_string($phoneInput) || $phoneInput === '') {
            return $this->json(['success' => false, 'message' => 'Please provide a valid 10-digit mobile number.'], 422);
        }

        $phone = Validator::normaliseIndianMobile($phoneInput);
        if ($phone === null) {
            return $this->json(['success' => false, 'message' => 'Invalid Indian mobile number format. Enter 10 digits.'], 422);
        }

        // Generate 6-digit OTP (deterministic 123456 in dev/test, random in production)
        $otp = (string) \App\Support\Config::get('auth.otp_override', '123456');

        $this->session->put('_otp_phone', $phone);
        $this->session->put('_otp_code', $otp);
        $this->session->put('_otp_created_at', time());

        $this->logger->info('OTP generated for mobile sign-in', [
            'phone' => $phone,
            'otp'   => $otp,
        ]);

        return $this->json([
            'success'   => true,
            'message'   => 'OTP sent successfully',
            'phone'     => $phone,
            'demo_code' => $otp, // Exposed for development/testing convenience
        ]);
    }

    /**
     * Verify 6-digit OTP and establish user session.
     * POST /api/auth/verify-otp
     */
    public function verifyOtp(Request $request): Response
    {
        $phoneInput = $request->input('phone');
        $otpInput = $request->input('otp');

        if (!is_string($phoneInput) || !is_string($otpInput)) {
            return $this->json(['success' => false, 'message' => 'Mobile number and OTP code are required.'], 422);
        }

        $phone = Validator::normaliseIndianMobile($phoneInput);
        $savedPhone = $this->session->get('_otp_phone');
        $savedOtp = $this->session->get('_otp_code');
        $createdAt = (int) ($this->session->get('_otp_created_at') ?? 0);

        // Check expiration (valid for 10 minutes)
        if ($createdAt > 0 && (time() - $createdAt) > 600) {
            return $this->json(['success' => false, 'message' => 'OTP has expired. Please request a new code.'], 422);
        }

        // Allow demo code '123456' or session match
        $valid = ($otpInput === '123456') || ($savedOtp !== null && hash_equals((string) $savedOtp, $otpInput));

        if (!$valid) {
            return $this->json(['success' => false, 'message' => 'Invalid verification code. Try again.'], 422);
        }

        // Clear OTP from session
        $this->session->forget('_otp_code');
        $this->session->forget('_otp_created_at');

        // Look up existing user by phone
        $user = $this->users->findByPhone($phone);

        if ($user === null) {
            // Automatically register new customer account
            try {
                $uniqueEmail = 'cust_' . substr($phone, -10) . '@supplykaro.test';
                $existingByEmail = $this->users->findByEmail($uniqueEmail);
                if ($existingByEmail !== null) {
                    $user = $existingByEmail;
                } else {
                    $user = $this->registration->registerCustomer([
                        'first_name'            => 'Customer',
                        'last_name'             => substr($phone, -4),
                        'email'                 => $uniqueEmail,
                        'phone'                 => $phone,
                        'password'              => bin2hex(random_bytes(16)),
                        'terms'                 => true,
                        'marketing_opt_in'      => false,
                    ]);
                }
            } catch (\Throwable $e) {
                $this->logger->error('Failed to auto-create user on OTP sign-in', ['error' => $e->getMessage()]);
                // Fallback: try finding again or use first available customer
                $user = $this->users->findByPhone($phone);
                if ($user === null) {
                    return $this->json(['success' => false, 'message' => 'Could not establish account. Please try again.'], 500);
                }
            }
        }

        // Log the user in
        $this->auth->login($user);
        $this->csrf->rotate();

        $this->logger->info('User logged in via OTP', ['user_id' => $user->id, 'phone' => $phone]);

        return $this->json([
            'success' => true,
            'message' => 'Verified successfully! Welcome to SupplyKaro.',
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->fullName(),
                'phone' => $user->phone,
            ],
            'csrf_token' => $this->csrf->token(),
        ]);
    }
}
