<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class HomeControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/home');

        // Vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Vérifie qu'il y a au moins un produit sur la page
        $products = $crawler->filter('.card');
        $this->assertGreaterThan(0, $products->count());
    }
}