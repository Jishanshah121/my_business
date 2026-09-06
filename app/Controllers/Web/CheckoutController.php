<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Request;
use App\Http\Response;
use App\Repositories\OrderRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\Gate;
use App\Support\Csrf;
use App\Support\Session\Session;
use App\Support\View;

final class CheckoutController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        AuthService $auth,
        Gate $gate,
        Csrf $csrf,
        private readonly OrderRepository $orders,
    ) {
        parent::__construct($view, $session, $auth, $gate, $csrf);
    }

    public function index(): Response
    {
        return $this->render('checkout/index', [
            'title' => 'Secure GST Checkout · SupplyKaro',
        ]);
    }

    public function placeOrder(Request $request): Response
    {
        $user = $this->auth->user();
        if ($user === null) {
            return $this->json(['error' => 'Please log in to complete your checkout.'], 401);
        }

        $items = $request->input('items', []);
        if (empty($items) || !is_array($items)) {
            return $this->json(['error' => 'Your shopping basket is empty.'], 422);
        }

        $shipping = [
            'contact_name'  => (string) $request->input('contact_name', $user->fullName()),
            'contact_phone' => (string) $request->input('contact_phone', $user->phone ?? '9876543210'),
            'line1'         => (string) $request->input('line1', 'Commercial Unit'),
            'city'          => (string) $request->input('city', 'Bokaro Steel City'),
            'state_code'    => (string) $request->input('state_code', '20'),
            'state_name'    => (string) $request->input('state_name', 'Jharkhand'),
            'pincode'       => (string) $request->input('pincode', '827001'),
            'gstin'         => $request->input('gstin'),
            'company_name'  => $request->input('company_name'),
            'landmark'      => $request->input('landmark'),
        ];

        $paymentMethod = (string) $request->input('payment_method', 'Razorpay UPI');

        $order = $this->orders->createFromCart($user->id, $items, $shipping, $paymentMethod);

        $this->success("Order {$order['order_number']} confirmed! It has been dispatched to warehouse packing.");

        return $this->json([
            'success'      => true,
            'order_id'     => $order['id'],
            'order_number' => $order['order_number'],
            'redirect'     => '/account#tab-orders',
        ]);
    }
}
