<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\WebTestCase;

/**
 * Class PagesAvailabilityTest.
 */
class PagesAvailabilityTest extends WebTestCase
{
    /**
     * @test
     */
    public function pagesAreAvailable()
    {
        $client = static::createClient();

        foreach ($this->urlsToTest() as $url) {
            $client->request('GET', $url);
            $this->assertTrue($client->getResponse()->isSuccessful(), sprintf('Url %s is unavailable', $url));
        }
    }

    /**
     * @return array
     */
    private function urlsToTest(): array
    {
        return [
            '/doc', // to catch errors in swagger annotations
        ];
    }
}
