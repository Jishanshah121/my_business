<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\BusinessProfileRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Services\Auth\RegistrationService;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;
use App\Validators\Validator;

final class BusinessRegisterController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly RegistrationService $registration,
        private readonly UserRepository $users,
        private readonly BusinessProfileRepository $businesses,
        private readonly SettingsRepository $settings,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function show(): Response
    {
        return $this->render('auth/register-business', [
            'title'         => 'Open a business account',
            'businessTypes' => $this->businesses->businessTypes(),
        ]);
    }

    public function store(Request $request): Response
    {
        $input = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation',
            'company_name', 'contact_person', 'contact_phone', 'business_type_id',
            'gstin', 'pan', 'fssai_licence', 'expected_monthly_spend', 'terms',
        ]);

        // Admin-configurable: some wholesale customers are legitimately
        // unregistered for GST, so this cannot be a constant.
        $requireGstin = $this->settings->bool('b2b', 'require_gstin', true);

        $validator = Validator::make($input, [
            'first_name'             => 'required|name|max:96',
            'last_name'              => 'nullable|name|max:96',
            'email'                  => 'required|email|max:191',
            'phone'                  => 'nullable|phone',
            'password'               => 'required|password|confirmed|max:200',
            'company_name'           => 'required|max:191|min:2',
            'contact_person'         => 'required|name|max:128',
            'contact_phone'          => 'required|phone',
            'business_type_id'       => 'required|integer',
            'gstin'                  => ($requireGstin ? 'required' : 'nullable') . '|gstin',
            'pan'                    => 'nullable|pan',
            'fssai_licence'          => 'nullable|alpha_num|max:20',
            'expected_monthly_spend' => 'nullable|numeric|between:0,100000000',
            'terms'                  => 'accepted',
        ], [
            'first_name'             => 'First name',
            'email'                  => 'Email address',
            'company_name'           => 'Company name',
            'contact_person'         => 'Contact person',
            'contact_phone'          => 'Contact number',
            'business_type_id'       => 'business type',
            'gstin'                  => 'GSTIN',
            'pan'                    => 'PAN',
            'fssai_licence'          => 'FSSAI licence',
            'expected_monthly_spend' => 'Expected monthly purchase',
            'terms'                  => 'terms and conditions',
        ]);

        $validator->after('email', function (mixed $email): ?string {
            return is_string($email) && $email !== '' && $this->users->emailExists($email)
                ? 'An account already exists with that email. Sign in and add your business details from your account.'
                : null;
        });

        $validator->after('gstin', function (mixed $gstin): ?string {
            return is_string($gstin) && $gstin !== '' && $this->businesses->gstinExists($gstin)
                ? 'That GSTIN is already registered with us. Contact sales@supplykaro.test if this is your business.'
                : null;
        });

        $validator->after('business_type_id', function (mixed $id): ?string {
            $valid = array_column($this->businesses->businessTypes(), 'id');

            return in_array((int) $id, array_map('intval', $valid), true) ? null : 'Choose a valid business type.';
        });

        $data = $validator->validate();

        $user = $this->registration->registerBusiness($data);
        $this->auth->login($user);

        $this->success(
            'Your account is ready and your business details are with our team. '
            . 'You can order at standard prices right away — trade pricing unlocks once we approve the account, usually within one working day.'
        );

        return $this->redirectAfterPost('/account/business');
    }
}
