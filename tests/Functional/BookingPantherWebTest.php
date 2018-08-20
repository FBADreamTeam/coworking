<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 19/08/2018
 * Time: 20:10.
 */

namespace App\Tests\Functional;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Panther\PantherTestCase;

class BookingPantherWebTest extends PantherTestCase
{
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

    public function testBookADateAndARoom(): void
    {
        $client = static::createPantherClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/booking');

        // click next day by default
        $crawler->findElement(WebDriverBy::className('fc-next-button'))->click();
        $client->wait(5, 1000)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('fc-bg'))
        );

        // click all-day on calendar
        $node = $crawler->findElement(WebDriverBy::className('fc-day-grid'));
        $node->click();

        $client->wait(5, 1000)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('card'))
        );

        // click first room book button
        $client->findElement(WebDriverBy::linkText('Réserver'))->click();

        sleep(3);

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatus());
        $form = $client->findElement(WebDriverBy::name('booking_add_options'));
        $this->assertEquals('form', $form->getTagName());

        // View options
        $card = $client->findElement(WebDriverBy::className('card'));
        // open accordion
        $card->findElement(WebDriverBy::className('btn-link'))->click();
        sleep(2);
        // add option
        $card->findElement(WebDriverBy::className('btn-increment'))->click();
        sleep(2);

        // check price
        $price = $client->findElement(WebDriverBy::id('price-placeholder'))->getText();
        $this->assertContains('20,00', $price);

        // click book button
        $client->findElement(WebDriverBy::id('booking_add_options_submit'))->submit();
        sleep(4);

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('form', $client->findElement(WebDriverBy::name('customer_login'))->getTagName());

        $client->findElement(WebDriverBy::id('customer_login_email'))->sendKeys('alex@test.xyz');
        $client->findElement(WebDriverBy::id('customer_login_password'))->sendKeys('testtest');
        $client->findElement(WebDriverBy::id('customer_login_submit'))->submit();
        sleep(4);

        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('form', $client->findElement(WebDriverBy::name('order'))->getTagName());

        // select address
        $client->findElement(WebDriverBy::className('form-check-input'))->click();
        $client->findElement(WebDriverBy::id('order_submit'))->submit();

        sleep(5);
        /** @var Response $response */
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatus());
        $title = $client->findElement(WebDriverBy::cssSelector('h3.display-4'));
        $this->assertEquals('VOTRE RÉSERVATION A BIEN ÉTÉ ENREGISTRÉE.', $title->getText());
    }
}
