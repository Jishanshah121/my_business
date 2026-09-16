<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Http\Response;
use App\Services\Catalog\CatalogService;

final class HomeController extends Controller
{
    public function index(CatalogService $catalog): Response
    {
        $primaryCategories = $catalog->getPrimaryCategories();
        $bestSellers = $catalog->getBestsellers(8);
        $newArrivals = $catalog->loadFullCatalog();
        $featured = $catalog->getFeaturedProducts(8);

        $heroSlides = [
            [
                'id'       => 1,
                'title'    => 'Everything you need to serve, pack & celebrate.',
                'subtitle' => 'From everyday business supplies to 10,000-guest celebrations — get everything in one place.',
                'ctaText'  => 'Shop Now',
                'ctaUrl'   => '/categories',
                'secText'  => 'Plan Your Event',
                'secUrl'   => '/party-box',
                'image'    => '/assets/images/hero/hero-serve-pack.webp',
            ],
            [
                'id'       => 2,
                'title'    => 'Business Supplies, Delivered to Your Kitchen.',
                'subtitle' => 'Wholesale food packaging, leak-proof meal boxes, and beverage supplies with 100% GST input credit.',
                'ctaText'  => 'Explore B2B Supplies',
                'ctaUrl'   => '/b2b',
                'secText'  => 'Request Bulk Quote',
                'secUrl'   => '/b2b#quote-form',
                'image'    => '/assets/images/hero/hero-business.webp',
            ],
            [
                'id'       => 3,
                'title'    => 'Planning a Wedding or Celebration?',
                'subtitle' => 'Tell us your guest count and our smart calculator will build your complete zero-shortage tableware kit.',
                'ctaText'  => 'Build Wedding Kit',
                'ctaUrl'   => '/party-box',
                'secText'  => 'Open Calculator',
                'secUrl'   => '/event-calculator',
                'image'    => '/assets/images/hero/hero-wedding.webp',
            ],
            [
                'id'       => 4,
                'title'    => 'Bulk Supply. Direct Factory Prices.',
                'subtitle' => 'Save up to 35% on master cartons of ripple cups, areca palm plates, and brown kraft bags.',
                'ctaText'  => 'View Volume Slabs',
                'ctaUrl'   => '/b2b',
                'secText'  => 'Sample Box Request',
                'secUrl'   => '/b2b#quote-form',
                'image'    => '/assets/images/hero/hero-bulk-supply.webp',
            ],
        ];

        $businessNeeds = [
            [
                'name'  => 'Restaurants & Dine-in',
                'desc'  => 'Tableware, takeaway packaging, tissues and dine-in essentials.',
                'cta'   => 'Shop Restaurant Supplies →',
                'url'   => '/category/tableware',
                'image' => '/assets/images/business/business-restaurants.webp',
            ],
            [
                'name'  => 'Cafes & Bakeries',
                'desc'  => 'Ripple cups, pastry boxes, wooden stirrers & cake boards.',
                'cta'   => 'Shop Cafe Supplies →',
                'url'   => '/category/cups-beverage',
                'image' => '/assets/images/business/business-cafes.webp',
            ],
            [
                'name'  => 'Cloud Kitchens',
                'desc'  => 'Leakproof meal trays, tamper-evident tape & kraft bags.',
                'cta'   => 'Shop Kitchen Packaging →',
                'url'   => '/category/food-packaging',
                'image' => '/assets/images/business/business-cloud-kitchens.webp',
            ],
            [
                'name'  => 'Catering & Events',
                'desc'  => 'Areca palm platters, buffet bowls, napkins & garbage bags.',
                'cta'   => 'Shop Catering Supplies →',
                'url'   => '/category/tableware',
                'image' => '/assets/images/business/business-catering.webp',
            ],
        ];

        $occasions = [
            ['name' => 'Wedding Reception', 'desc' => 'Complete tableware for 500+ guests.', 'cta' => 'Build Wedding Kit', 'tag' => 'Weddings', 'url' => '/party-box'],
            ['name' => 'Birthday & House Party', 'desc' => 'Snack plates, ripple cups & napkins.', 'cta' => 'Build Party Box', 'tag' => 'Celebrations', 'url' => '/party-box'],
            ['name' => 'Community Pooja / Bhandara', 'desc' => 'Dona, pattal plates & prasad bags.', 'cta' => 'Bhandara Packs', 'tag' => 'Ceremonies', 'url' => '/event-calculator'],
            ['name' => 'Corporate Events & Hi-Tea', 'desc' => 'Coffee cups, stirrers & lunch boxes.', 'cta' => 'Corporate Kits', 'tag' => 'Corporate', 'url' => '/party-box'],
        ];

        return $this->render('home/index', [
            'title'             => 'SupplyKaro · Har Supply, Ek Jagah.',
            'heroSlides'        => $heroSlides,
            'primaryCategories' => $primaryCategories,
            'bestSellers'       => $bestSellers,
            'newArrivals'       => $newArrivals,
            'featured'          => $featured,
            'businessNeeds'     => $businessNeeds,
            'occasions'         => $occasions,
        ]);
    }
}
