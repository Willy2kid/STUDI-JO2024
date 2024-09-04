<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\BrowserKit\Cookie;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginWithGuestOrder()
    {
        $client = static::createClient();
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/login');
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Connexion avec login et mot de passe
        $form = $crawler->selectButton('Se connecter')->form();
        $form['username'] = 'user';
        $form['password'] = 'user';
        $client->submit($form);

        // Double authentification
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneByUsername('user');
        $authCode = $user->getEmailAuthCode();
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Valider')->form();
        $form['authcode'] = $authCode;
        $client->submit($form);

        // Vérifier "Vous êtes connecté sur le compte user"
        $crawler = $client->request('GET', '/login');
        $this->assertStringContainsString('Vous êtes connecté sur le compte user', $crawler->html());
    }

    public function testAddGuestCart()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(\App\Entity\User::class)->findOneByUsername('user');
        $client->loginUser($user);
        $user->getOrder()->removeItems();
        
        // Créer un panier
        $order = new Order();
        $order->setCreatedAt(new \DateTime());
        $order->setUpdatedAt(new \DateTime());
        $entityManager->persist($order);
        $entityManager->flush();
        $orderId = $order->getId();

        // Ajouter un produit au panier
        $product = $entityManager->getRepository(Product::class)->find(1);
        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setQuantity(1);
        $orderItem->setOffer('solo');
        $order->addItem($orderItem);
        $entityManager->persist($orderItem);
        $entityManager->flush();

        // Ajout du cookie pour le checkout
        $checkoutCookie = new Cookie('checkout', 'true');
        $client->getCookieJar()->set($checkoutCookie);

        // Ajout du cookie avec l'id du panier du guest user
        $cookie = new Cookie('cart_id', $orderId);
        $client->getCookieJar()->set($cookie);

        $client->request('GET', '/login_in_progress');
        $crawler = $client->followRedirect();

        $this->assertEquals('/cart', $client->getRequest()->getPathInfo());
        $this->assertCount(1, $crawler->filter('li.item-in-cart'));
    }

    public function testLogout()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(\App\Entity\User::class)->findOneByUsername('user');
        $client->loginUser($user);

        $client->request('GET', '/logout');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}