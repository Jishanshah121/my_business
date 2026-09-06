<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Exceptions\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\BusinessProfileRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Services\Notification\NotificationDispatcher;
use App\Support\Csrf;
use App\Support\Database;
use App\Support\Logger;
use App\Support\Session\Session;
use App\Support\View;
use App\Validators\Validator;

/**
 * The B2B approval queue.
 *
 * Approving moves the customer onto a B2B pricing group; rejecting returns
 * them to retail. Both write an admin_logs entry, because a pricing-tier
 * change needs to be attributable.
 */
final class BusinessApprovalController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly BusinessProfileRepository $businesses,
        private readonly UserRepository $users,
        private readonly NotificationDispatcher $notifications,
        private readonly Logger $logger,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'approved', 'rejected', 'suspended'], true)) {
            $status = 'pending';
        }

        return $this->render('admin/business-approvals', [
            'title'    => 'Business accounts',
            'status'   => $status,
            'profiles' => $this->businesses->paginateByStatus($status, 50),
            'counts'   => [
                'pending'  => $this->businesses->countByStatus('pending'),
                'approved' => $this->businesses->countByStatus('approved'),
                'rejected' => $this->businesses->countByStatus('rejected'),
            ],
        ]);
    }

    public function approve(Request $request, string $id): Response
    {
        $profile = $this->businesses->find((int) $id);
        if ($profile === null) {
            throw new HttpException(404, 'That business account no longer exists.');
        }

        $group = (string) $request->input('customer_group', 'b2b_standard');
        if (!in_array($group, ['b2b_standard', 'b2b_gold', 'distributor'], true)) {
            $group = 'b2b_standard';
        }

        $actorId = $this->auth->id() ?? 0;
        $this->businesses->approve($profile->id, $actorId, $group);
        $this->audit('b2b.approve', $profile->id, "Approved {$profile->companyName} onto {$group}");

        $user = $this->users->find($profile->userId);
        if ($user !== null) {
            $this->notifications->email(
                event: 'business.approved',
                recipient: $user->email,
                subject: 'Your SupplyKaro business account is approved',
                template: 'business-approved',
                data: ['user' => $user, 'profile' => $profile],
                userId: $user->id,
                referenceType: 'business_profile',
                referenceId: $profile->id,
            );
        }

        $this->success($profile->companyName . ' approved. Trade pricing is now active for that account.');

        return $this->redirectAfterPost('/admin/business-accounts');
    }

    public function reject(Request $request, string $id): Response
    {
        $profile = $this->businesses->find((int) $id);
        if ($profile === null) {
            throw new HttpException(404, 'That business account no longer exists.');
        }

        $data = Validator::make(
            $request->only(['reason']),
            ['reason' => 'required|min:10|max:500'],
            ['reason' => 'Reason']
        )->messages([
            'reason.required' => 'Give a reason — the customer sees this.',
            'reason.min'      => 'Give the customer enough detail to fix the problem.',
        ])->validate();

        $actorId = $this->auth->id() ?? 0;
        $this->businesses->reject($profile->id, $actorId, (string) $data['reason']);
        $this->audit('b2b.reject', $profile->id, "Rejected {$profile->companyName}: {$data['reason']}");

        $user = $this->users->find($profile->userId);
        if ($user !== null) {
            $this->notifications->email(
                event: 'business.rejected',
                recipient: $user->email,
                subject: 'About your SupplyKaro business account',
                template: 'business-rejected',
                data: ['user' => $user, 'profile' => $profile, 'reason' => $data['reason']],
                userId: $user->id,
                referenceType: 'business_profile',
                referenceId: $profile->id,
            );
        }

        $this->success($profile->companyName . ' was not approved. The customer has been told why.');

        return $this->redirectAfterPost('/admin/business-accounts');
    }

    private function audit(string $action, int $targetId, string $description): void
    {
        Database::connection()->prepare(
            'INSERT INTO `admin_logs` (`user_id`, `action`, `target_type`, `target_id`, `description`, `ip_address`)
             VALUES (:u, :a, :tt, :ti, :d, :ip)'
        )->execute([
            'u'  => $this->auth->id(),
            'a'  => $action,
            'tt' => 'business_profile',
            'ti' => $targetId,
            'd'  => $description,
            'ip' => @inet_pton($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') ?: inet_pton('0.0.0.0'),
        ]);

        $this->logger->info('Admin action: ' . $action, [
            'admin_id' => $this->auth->id(),
            'target'   => $targetId,
        ]);
    }
}
