<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\AddressRepository;
use App\Repositories\BusinessProfileRepository;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;
use App\Validators\Validator;

final class AccountController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly UserRepository $users,
        private readonly BusinessProfileRepository $businesses,
        private readonly LoginAttemptRepository $attempts,
        private readonly AddressRepository $addresses,
        private readonly OrderRepository $orders,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function dashboard(): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $profile = $this->businesses->findByUser($user->id);
        $attempts = $this->attempts->recentForIdentifier($user->email, 6);
        $userAddresses = $this->addresses->forUser($user->id);
        $userOrders = $this->orders->forUser($user->id);

        return $this->render('account/dashboard', [
            'title'     => 'My Account · SupplyKaro',
            'profile'   => $profile,
            'attempts'  => $attempts,
            'addresses' => $userAddresses,
            'orders'    => $userOrders,
        ]);
    }

    public function profile(): Response
    {
        return $this->render('account/profile', ['title' => 'Your details']);
    }

    public function updateProfile(Request $request): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $validator = Validator::make(
            $request->only(['first_name', 'last_name', 'phone', 'marketing_opt_in']),
            [
                'first_name' => 'required|name|max:96',
                'last_name'  => 'nullable|name|max:96',
                'phone'      => 'nullable|phone',
            ],
            ['first_name' => 'First name', 'last_name' => 'Last name', 'phone' => 'Mobile number']
        );

        $validator->after('phone', function (mixed $phone) use ($user): ?string {
            if (!is_string($phone) || $phone === '') {
                return null;
            }
            $normalised = Validator::normaliseIndianMobile($phone);
            $existing = $this->users->findByPhone($normalised);

            return $existing !== null && $existing->id !== $user->id
                ? 'That mobile number is registered to another account.'
                : null;
        });

        $data = $validator->validate();

        $this->users->update($user->id, [
            'first_name'       => trim((string) $data['first_name']),
            'last_name'        => isset($data['last_name']) && $data['last_name'] !== '' ? trim((string) $data['last_name']) : null,
            'phone'            => isset($data['phone']) && $data['phone'] !== ''
                                  ? Validator::normaliseIndianMobile((string) $data['phone']) : null,
            'marketing_opt_in' => $request->boolean('marketing_opt_in') ? 1 : 0,
        ]);

        $this->success('Your details have been saved.');

        return $this->redirectAfterPost('/account/profile');
    }

    public function security(): Response
    {
        $user = $this->auth->user();

        return $this->render('account/security', [
            'title'    => 'Sign-in and security',
            'attempts' => $user === null ? [] : $this->attempts->recentForIdentifier($user->email, 8),
        ]);
    }

    public function updatePassword(Request $request): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $validator = Validator::make(
            $request->only(['current_password', 'password', 'password_confirmation']),
            [
                'current_password' => 'required|max:200',
                'password'         => 'required|password|confirmed|max:200|different:current_password',
            ],
            ['current_password' => 'Current password', 'password' => 'New password']
        );

        $validator->after('current_password', static function (mixed $current) use ($user): ?string {
            return is_string($current) && password_verify($current, $user->passwordHash)
                ? null
                : 'That is not your current password.';
        });

        $data = $validator->validate();

        $this->users->updatePassword($user->id, $this->auth->hashPassword((string) $data['password']));

        // Other devices are signed out; this one stays in, with a fresh stamp.
        $destroyed = $this->auth->logoutOtherSessions();
        $refreshed = $this->users->find($user->id);
        if ($refreshed !== null) {
            $this->auth->login($refreshed);
        }

        $this->success(
            $destroyed > 0
                ? "Password changed. You have been signed out of {$destroyed} other device" . ($destroyed === 1 ? '.' : 's.')
                : 'Password changed.'
        );

        return $this->redirectAfterPost('/account/security');
    }

    public function signOutOtherSessions(): Response
    {
        $count = $this->auth->logoutOtherSessions();

        $this->success($count > 0
            ? "Signed out of {$count} other device" . ($count === 1 ? '.' : 's.')
            : 'There were no other active sessions.');

        return $this->redirectAfterPost('/account/security');
    }

    public function business(): Response
    {
        $user = $this->auth->user();
        $profile = $user === null ? null : $this->businesses->findByUser($user->id);

        return $this->render('account/business', [
            'title'   => 'Business account',
            'profile' => $profile,
        ]);
    }

    public function storeAddress(Request $request): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $validator = Validator::make($request->all(), [
            'contact_name'  => 'required|max:128',
            'contact_phone' => 'required',
            'line1'         => 'required|max:191',
            'city'          => 'required|max:96',
            'pincode'       => 'required',
        ]);

        if ($validator->fails()) {
            $this->error('Please fill in all required address fields.');
            return $this->redirectAfterPost('/account#tab-addresses');
        }

        $data = $validator->validate();
        $data['company_name'] = $request->input('company_name');
        $data['gstin'] = $request->input('gstin');
        $data['line2'] = $request->input('line2');
        $data['landmark'] = $request->input('landmark');
        $data['label'] = $request->input('label') ?: 'Main Outlet';
        $data['state_name'] = $request->input('state_name') ?: 'Jharkhand';
        $data['state_code'] = $request->input('state_code') ?: '20';
        $data['is_default_shipping'] = $request->boolean('is_default_shipping');

        $this->addresses->create($user->id, $data);
        $this->success('Delivery address added successfully.');

        return $this->redirectAfterPost('/account#tab-addresses');
    }

    public function updateAddress(Request $request, int $id): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $validator = Validator::make($request->all(), [
            'contact_name'  => 'required|max:128',
            'contact_phone' => 'required',
            'line1'         => 'required|max:191',
            'city'          => 'required|max:96',
            'pincode'       => 'required',
        ]);

        if ($validator->fails()) {
            $this->error('Please fill in all required address fields.');
            return $this->redirectAfterPost('/account#tab-addresses');
        }

        $data = $validator->validate();
        $data['company_name'] = $request->input('company_name');
        $data['gstin'] = $request->input('gstin');
        $data['line2'] = $request->input('line2');
        $data['landmark'] = $request->input('landmark');
        $data['label'] = $request->input('label') ?: 'Main Outlet';
        $data['state_name'] = $request->input('state_name') ?: 'Jharkhand';
        $data['state_code'] = $request->input('state_code') ?: '20';
        $data['is_default_shipping'] = $request->boolean('is_default_shipping');

        $this->addresses->update($id, $user->id, $data);
        $this->success('Delivery address updated successfully.');

        return $this->redirectAfterPost('/account#tab-addresses');
    }

    public function setDefaultAddress(int $id): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $this->addresses->setDefault($id, $user->id);
        $this->success('Primary delivery address updated.');

        return $this->redirectAfterPost('/account#tab-addresses');
    }

    public function deleteAddress(int $id): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->redirect('/login');
        }

        $this->addresses->delete($id, $user->id);
        $this->success('Address removed.');

        return $this->redirectAfterPost('/account#tab-addresses');
    }
}
