<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 19/08/2018
 * Time: 16:39.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookingWebTest extends WebTestCase
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

    public function testBookingIndexIsReachable(): void
    {
        $client = static::createClient();
        $client->followRedirects();
        $crawler = $client->request('GET', '/booking');

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(1, $crawler->filter('#calendar')->count());
    }

    public function testFilterRouteIsReachable(): void
    {
        $client = static::createClient();
        $client->xmlHttpRequest('GET', '/booking/filter', ['startDate' => '2018-08-06 00:00:00', 'endDate' => '2018-08-10 00:00:00']);

        /** @var Response $response */
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);
    }
}
