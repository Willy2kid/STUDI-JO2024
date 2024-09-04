<?php

namespace App\Tests\Controller;

// use App\Tests\CartAssertionsTrait;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Doctrine\ORM\EntityManagerInterface;

class CartControllerTest extends WebTestCase
{
    private $entityManager;

    private function addRandomProductToCart(AbstractBrowser $client)    // : string
    {
        $productId = rand(1, 9);
        $offer = 'solo';
        
        $client->request('POST', '/update-cart', [
            'add_to_cart' => [
                'productId' => $productId,
            ],
            'offer' => $offer,
        ]);

        // $crawler = $client->request('GET', '/shop');
        // // Sélectionne un bouton solo, duo, ou famille aléatoire
        // $buttons = $crawler->filter('button.update-cart-button');
        // $count = $buttons->count();
        // $randomIndex = rand(0, $count - 1);
        // $randomButton = $buttons->eq($randomIndex);
        // // Find the associated form
        // $formCrawler = $randomButton->closest('form');
        // $form = $formCrawler->form();
        // // Submit the form
        // $client->submit($form);
    }

    public function testCartIsEmpty()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/cart');

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('li.item-in-cart'));
    }

    public function testAddProductToCart()
    {
        $client = static::createClient();
        $this->addRandomProductToCart($client);
        $crawler = $client->request('GET', '/cart');

        // dump($crawler->html());

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('li.item-in-cart'));
    }

    public function testClearCart()
    {
        $client = static::createClient();
        $this->addRandomProductToCart($client);

        // Clear the cart
        $crawler = $client->request('GET', '/cart');
        $form = $crawler->selectButton('Clear')->form();
        $client->submit($form);

        // Request the cart page again to get the updated HTML content
        $crawler = $client->request('GET', '/cart');
        $this->assertCount(0, $crawler->filter('li.item-in-cart'));
    }

    public function testRemoveProductFromCart()
    {
        $client = static::createClient();
        $this->addRandomProductToCart($client);

        // Removes the product from the cart
        $client->request('GET', '/cart');
        $client->submitForm('Remove');

        // Request the cart page again to get the updated HTML content
        $crawler = $client->request('GET', '/cart');

        $this->assertCount(0, $crawler->filter('li.item-in-cart'));
    }

    public function testGuestPayment()
    {
        $client = static::createClient();
        $this->addRandomProductToCart($client);
        
        // Pay
        $client->request('GET', '/paiement');
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        // Vérifiez que vous êtes bien sur la page de connexion
        $crawler = $client->followRedirect();
        $this->assertEquals('/login', $client->getRequest()->getPathInfo());
    }

    public function testPaymentAsUser()
    {
        // Récupérez l'utilisateur existant
        $client = static::createClient();
        $this->entityManager = $client->getContainer()->get('doctrine.orm.default_entity_manager');
        $user = $this->entityManager->getRepository(\App\Entity\User::class)->findOneByUsername('user');
        $client->loginUser($user);
        
        $this->addRandomProductToCart($client);

        // Pay
        $crawler = $client->request('GET', '/cart');
        $link = $crawler->selectLink('Paiement')->link();
        $client->click($link);

        // Vérifiez que l'achat est complété
        // $crawler = $client->followRedirect();
        // $this->assertSelectorTextContains('div.alert.alert-success', 'Paiement réussi');

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/cart', $response->headers->get('Location'));
    }

    // public function testPaymentProcess()
    // {
    //     $client = static::createClient();
    // 
    //     // Add a product to the cart
    //     $this->addRandomProductToCart($client);
    // 
    //     // Go to the cart page
    //     $crawler = $client->request('GET', '/cart');
    // 
    //     // Click on the "Paiement" button as an unauthenticated user
    //     $link = $crawler->selectLink('Paiement')->link();
    //     $client->click($link);
    //     $client->followRedirects();
    // 
    //     dump($crawler->html());
    // 
    //     // Simulez la connexion d'un utilisateur
    //     $form = $crawler->selectButton('Se connecter')->form();
    //     $form['_username'] = 'user';
    //     $form['_password'] = 'user';
    //     $client->submit($form);
    // 
    //     // Should be redirected back to the cart page
    //     $this->assertResponseRedirects('/cart');
    // 
    //     // Click on the "Paiement" button again
    //     $crawler = $client->request('GET', '/cart');
    //     $link = $crawler->selectLink('Paiement')->link();
    //     $client->click($link);
    // 
    //     // Should be redirected to the payment server
    //     $this->assertResponseRedirects('/paiement');
    // 
    //     // Should be redirected back to the cart page with a success message
    //     $this->assertResponseRedirects('/cart');
    //     $crawler = $client->request('GET', '/cart');
    //     $this->assertContains('Paiement réussi.', $crawler->html());
    // }

    //  public function testCheckoutSuccess()
    //  {
    //      $client = static::createClient();
    //      $this->addRandomProductToCart($client);
    //  
    //      // Simulate a successful payment response
    //      $paymentResponse = new Response('Payment successful', 200);
    //      $this->getMockPaymentServer()->expects($this->once())
    //          ->method('handleRequest')
    //          ->willReturn($paymentResponse);
    //  
    //      $crawler = $client->request('GET', '/paiement');
    //  
    //      // Assert that the payment was successful
    //      $this->assertResponseIsSuccessful();
    //      $this->assertFlashMessage('success', 'Paiement réussi.');
    //  }

    //  public function testCheckout()
    //  {
    //      $client = static::createClient();
    //      $this->addRandomProductToCart($client);
    //  
    //      // Vérifiez que la page de paiement est accessible
    //      $crawler = $client->request('POST', '/paiement');
    //      dump($crawler->html());
    //  
    //      // Simulez la connexion d'un utilisateur
    //      $form = $crawler->selectButton('Se connecter')->form();
    //      $form['_username'] = 'user';
    //      $form['_password'] = 'user';
    //      $client->submit($form);
    //  
    //      $crawler = $client->request('GET', '/paiement');
    //  
    //      // Vérifiez que le paiement a été effectué avec succès
    //      $this->assertResponseRedirects('/cart');
    //      $crawler = $client->followRedirect();
    //      $this->assertResponseIsSuccessful();
    //      $this->assertSelectorTextContains('div.flash-success', 'Paiement réussi.');
    //  }
}
