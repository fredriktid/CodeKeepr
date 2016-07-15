<?php

namespace Frigg\KeeprBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DefaultControllerTest.
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * Tests basic functionality.
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertTrue($response->getStatusCode() == 200);

        $this->assertTrue($crawler->filter('html:contains("CodeKeepr")')->count() > 0);
    }
}
