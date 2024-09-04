<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class ShopControllerTest extends WebTestCase
{
    public function testAddRandomProductToCart()
    {
        $client = static::createClient();

        $productId = rand(1, 9);
        $offer = 'solo';
        
        $client->request('POST', '/update-cart', [
            'add_to_cart' => [
                'productId' => $productId,
            ],
            'offer' => $offer,
        ]);

        $crawler = $client->request('GET', '/shop');
        $this->assertGreaterThan(0, $crawler->filter('li.list-group-item:contains("Product ' . $productId . ' (offre solo)")')->count());    }
}