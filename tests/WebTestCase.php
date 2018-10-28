<?php

declare(strict_types=1);

namespace App\Tests;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Client as SymfonyClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyTestCase;

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

//    /**
//     * @param Client|null $client
//     *
//     * @return Client
//     */
//    protected function authenticateAdmin(Client $client = null): Client
//    {
//        /** @var Client $client */
//        if (null === $client) {
//            $client = static::createClient();
//        }
//        $client->followRedirects(false);
//        $crawler = $client->request('GET', '/login');
//
//        $admin = UserFixtures::getUserAdmin();
//        $form = $crawler->filter('[type=submit]')->form();
//        $client->submit($form, ['_username' => $admin->getEmail(), '_password' => $admin->getPlainPassword()]);
//
//        return $client;
//    }
//
//    /**
//     * @param string|null $login
//     * @param string      $password
//     *
//     * @return Client
//     */
//    protected function authenticateApi(string $login = null, string $password = null): Client
//    {
//        if (null === $login) {
//            $user = UserFixtures::getSellerUserForLogin();
//
//            $login = $user->getEmail();
//            $password = $user->getPlainPassword();
//        }
//
//        /** @var Client $client */
//        $client = static::createClient();
//        $client->sendJson(
//            '/api/en/login',
//            [
//                'login' => $login,
//                'password' => $password,
//            ]
//        );
//
//        $token = $client->getJsonResponse()['token'];
//        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
//
//        return $client;
//    }
}
