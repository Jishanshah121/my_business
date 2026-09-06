<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Response;

final class BusinessController extends Controller
{
    public function index(): Response
    {
        return $this->render('b2b/index', [
            'title' => 'B2B Wholesale Procurement & Bulk Quotes · SupplyKaro',
        ]);
    }
}
