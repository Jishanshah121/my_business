<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Response;
use App\Services\Catalog\CatalogService;

final class EventPlannerController extends Controller
{
    public function partyBox(CatalogService $catalog): Response
    {
        $plates = $catalog->getProducts('tableware');
        $cups = $catalog->getProducts('cups-beverage');
        $cutlery = $catalog->getProducts('cutlery');
        $tissues = $catalog->getProducts('tissues-hygiene');

        return $this->render('events/party-box', [
            'title'   => 'Build Your Party Box · Zero Shortage Guarantee',
            'plates'  => $plates,
            'cups'    => $cups,
            'cutlery' => $cutlery,
            'tissues' => $tissues,
        ]);
    }

    public function calculator(): Response
    {
        return $this->render('events/calculator', [
            'title' => 'Smart Catering & Event Consumables Calculator · SupplyKaro',
        ]);
    }
}
