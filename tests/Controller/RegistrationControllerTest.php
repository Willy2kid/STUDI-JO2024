<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegister()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Valider')->form();

        $form['registration_form[lastname]'] = 'Test';
        $form['registration_form[firstname]'] = 'Test';
        $form['registration_form[email]'] = 'test@example.com';
        $form['registration_form[plainPassword]'] = 'Test1234!';
        $form['registration_form[agreeTerms]'] = true;

        $client->submit($form);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        // Vérification que l'utilisateur existe
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);

        // Suppression de l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();
    }
}