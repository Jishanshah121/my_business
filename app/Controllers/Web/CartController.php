<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Response;

final class CartController extends Controller
{
    public function index(): Response
    {
        return $this->render('cart/index', [
            'title' => 'Shopping Cart & Pack Breakdown · SupplyKaro',
        ]);
    }
}
