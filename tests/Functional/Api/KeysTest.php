<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Client;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class KeysTest extends WebTestCase
{
    public const VALID_KEYS = [
        'publicKey' => 'adsf;aa46u39a9[erg',
        'encryptedPrivateKey' => 'qa93wu686vw486y7b48y',
    ];

    public const INVALID_KEYS = [
        'publicKey' => '',
        'encryptedPrivateKey' => '',
    ];

    /**
     * @test
     */
    public function wrongKeysSendFail()
    {
        $client = $this->authenticateApi();
        foreach (static::INVALID_KEYS as $key => $value) {
            $body = static::VALID_KEYS;
            $body[$key] = $value;

            $client->sendJson(
                '/api/keys',
                $body
            );

            $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponseCode());
        }
    }

    /**
     * @test
     */
    public function sendKeys()
    {
        $client = $this->authenticateApi();

        $client->sendJson(
            '/api/keys',
            static::VALID_KEYS
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $client->getResponseCode());
        $this->assertKeysStored($client);
    }

    private function assertKeysStored(Client $client)
    {
        $client->request(
            'GET',
            '/api/keys'
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponseCode());
        $this->assertEquals(static::VALID_KEYS, $client->getJsonResponse());
    }
}
