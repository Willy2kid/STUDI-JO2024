<?php

namespace App\Tests\Controller;

// use App\Controller\AdminController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class AdminControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.default_entity_manager');

        $adminUser = $this->entityManager->getRepository(\App\Entity\User::class)->findOneByUsername('admin');
        $this->client->loginUser($adminUser);
    }

    private function createProduct()
    {
        $crawler = $this->client->request('GET', '/admin/nouveau');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['product[name]'] = 'Test product';
        $form['product[description]'] = 'Test description';
        $form['product[datetime]'] = '2024-08-22T09:43';
        $form['product[price]'] = '10.99';
        $form['product[image]'] = null;

        $this->client->submit($form);

        // Get the created product from the database
        $product = $this->entityManager
            ->getRepository(\App\Entity\Product::class)
            ->findOneBy([], ['id' => 'DESC']);

        return $product;
    }

    public function testIndex()
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/admin');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAddProduct()
    {
        $product = $this->createProduct();

        // Assert that the product is present in the database
        $this->assertNotNull($product);
    }

    public function testEditProduct()
    {
        $product = $this->createProduct();
        $crawler = $this->client->request('GET', '/admin/' . $product->getId() . '/modifier');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['product[name]'] = 'Test product updated';
        $this->client->submit($form);

        // Vérifier que le nom du produit a été mis à jour
        $this->entityManager->clear();
        $updatedProduct = $this->entityManager
            ->getRepository(\App\Entity\Product::class)
            ->find($product->getId());

        $this->assertEquals('Test product updated', $updatedProduct->getName());
    }

    public function testDeleteProduct()
    {
        $product = $this->createProduct();
        $crawler = $this->client->request('GET', '/admin/' . $product->getId() . '/supprimer');

        // Assert that the product is no longer present in the database
        $this->entityManager->clear();
        $deletedProduct = $this->entityManager
            ->getRepository(\App\Entity\Product::class)
            ->find($product->getId());
        
        $this->assertNull($deletedProduct);
    }
}