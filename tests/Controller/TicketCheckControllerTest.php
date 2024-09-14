<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class TicketCheckControllerTest extends WebTestCase
{
    public function testValideTicket()
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(\App\Entity\User::class)->findOneByUsername('user');
        $client->loginUser($user);
        // $user->getOrder()->removeItems();
        
        // Créer un panier
        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTime());
        $order->setUpdatedAt(new \DateTime());
        $entityManager->persist($order);
        $entityManager->flush();
        $orderId = $order->getId();

        // Payer un produit pour générer son ticket et le QrCode
        $product = $entityManager->getRepository(Product::class)->find(1);
        $orderItem = new OrderItem();
        $orderItem->setProduct($product);
        $orderItem->setQuantity(1);
        $orderItem->setOffer('solo');
        $order
            ->addItem($orderItem)
            ->setUpdatedAt(new \DateTime());
        $entityManager->persist($orderItem);
        $entityManager->flush();
        $client->request('GET', '/paiement');

        // Générer le ticket complet et le vérifier
        $ticket = $user->getId() . '_' . $orderItem->getTicket();
        $crawler = $client->request('GET', '/vérification');
        $form = $crawler->selectButton('Vérifier')->form();
        $form['ticket_verification[qrCodeText]'] = $ticket;
        $client->submit($form);

        // Vérifier que le ticket est valide
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('Titulaire: John Doe', $crawler->html());
        $this->assertStringContainsString('Billet solo pour Product 1', $crawler->html());
    }

    public function testInvalidTicket()
    {
        $client = static::createClient();

        // Vérifier un faux ticket
        $ticket = '0191abbc-1ad1-7a0f-8213-1e70b4312864_18550b22-186f-49c6-8d75-f5d3a51102d8';
        $crawler = $client->request('GET', '/vérification');
        $form = $crawler->selectButton('Vérifier')->form();
        $form['ticket_verification[qrCodeText]'] = $ticket;
        $client->submit($form);

        // Vérifier que le ticket n'est pas valide
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('Invalid QR code', $crawler->html());
    }

    public function testWrongFormatTicket()
    {
        $client = static::createClient();

        // Vérifier un faux ticket
        $ticket = '1_18550b22-186f-49c6-8d75-f5d3a51102d8';
        $crawler = $client->request('GET', '/vérification');
        $form = $crawler->selectButton('Vérifier')->form();
        $form['ticket_verification[qrCodeText]'] = $ticket;
        $client->submit($form);

        // Vérifier que le ticket n'est pas valide
        $crawler = $client->followRedirect();
        $this->assertStringContainsString('Invalid QR code format : missing uuid', $crawler->html());
    }
}