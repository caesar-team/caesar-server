<?php

declare(strict_types=1);


namespace App\Tests\Functional\Api;

use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RestorePasswordTest extends WebTestCase
{
    public function initRestorePasswordTest()
    {
        $client = $this->authenticateApi();
        $client->request('GET', '/api/srp/password/restore');
        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponseCode());
    }
}