<?php

declare(strict_types=1);

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client as SymfonyClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WebTestCase.
 */
class WebTestCase extends SymfonyTestCase
{
    /**
     * @param array $options
     * @param array $server
     *
     * @return SymfonyClient|Client
     */
    public static function createClient(array $options = [], array $server = [])
    {
        return parent::createClient($options, $server);
    }

    /**
     * @param string $className
     *
     * @return EntityRepository
     */
    protected function getRepository(string $className): EntityRepository
    {
        self::bootKernel();

        return static::$kernel->getContainer()->get('doctrine')->getRepository($className);
    }

    /**
     * @param Client|null $client
     *
     * @return Client
     */
    protected function authenticateAdmin(Client $client = null): Client
    {
        /** @var Client $client */
        if (null === $client) {
            $client = static::createClient();
        }

        $client->request('GET', '/doc', [], [], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => $_ENV['ADMIN_PASS'],
        ]);

        $this->assertEquals(Response::HTTP_OK, $client->getResponseCode());

        return $client;
    }

    /**
     * @param string $username
     *
     * @return Client
     */
    protected function authenticateApi(string $username = UserFixtures::API_CLIENT_NAME): Client
    {
        $user = $this->getRepository(User::class)->findOneBy(['username' => $username]);

        /** @var Client $client */
        $client = static::createClient();
        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $user->getToken()));

        return $client;
    }
}
