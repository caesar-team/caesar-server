<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListTreeTest extends WebTestCase
{
    /**
     * @test
     */
    public function listTreeTest()
    {
        $client = $this->authenticateApi();
        $client->request('GET', '/api/list');

        $this->assertEquals(Response::HTTP_OK, $client->getResponseCode());
        $this->assertCount(3, $client->getJsonResponse());
    }
}
