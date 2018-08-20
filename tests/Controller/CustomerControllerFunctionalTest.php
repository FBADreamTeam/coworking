<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class CustomerControllerFunctionalTest extends WebTestCase
{
    /**
     * Called before doing test.
     *
     * @throws \Exception
     */
    public static function setUpBeforeClass()
    {
        $client = self::createClient();
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);
        $application->run(new StringInput('doctrine:database:drop --env=test --force'));
        $application->run(new StringInput('doctrine:database:create --env=test'));
        $application->run(new StringInput('doctrine:schema:update --env=test --force'));
        $application->run(new StringInput('doctrine:fixtures:load --env=test'));
    }

    public function testLoginPageIsUp()
    {
        $client = static::createClient();
        $client->request('GET', '/profile/login'); // Envoi de la requette

        $response = $client->getResponse()->getStatusCode(); // Récupère le status de la réponse
        $this->assertSame(200, $response);
    }

    // test la présence du bouton Connexion dans la page
    public function testContentLoginPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/profile/login'); // Le craler nous permet de naviguer dans le DOM

        $this->assertSame(1, $crawler->filter('html:contains("Connexion")')->count());
    }

    public function testCreateCustomerFront()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/profile/new'); // Le crawler nous permet de naviguer dans le DOM

        $form = $crawler->selectButton('Valider')->form();
        $form['customer[firstName]'] = 'brahim Test';
        $form['customer[lastName]'] = 'louridi Test';
        $form['customer[addresses][0][street]'] = 'rue des fous';
        $form['customer[addresses][0][postalCode]'] = 77130;
        $form['customer[addresses][0][city]'] = 'MELUN';
        $form['customer[addresses][0][country]'] = 'France';
        $form['customer[addresses][0][addressCpl]'] = 'bla bla 10';
        $form['customer[email]'] = 'test'.rand(0, 100).'@test'.rand(0, 100).'.com';
        $form['customer[password]'] = 'testtest';
        $form['customer[password_confirm]'] = 'testtest';

        $client->submit($form);

        $client->followRedirect(); // On doit suivre la redirection lors de l'inscription du user pour récupérer le bon contenu

        $this->assertContains('Votre compte a bien été créé', $client->getResponse()->getContent());
    }
}
