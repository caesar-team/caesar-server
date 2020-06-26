<?php

namespace App\Tests\Helper;

use FOS\UserBundle\Model\UserInterface;

class Api extends \Codeception\Module
{
    private const SCHEMA_FOLDER = '/tests/_support/schemas/';

    public function getToken(UserInterface $user): string
    {
        $jwtManager = $this->getSymfony()->grabService('lexik_jwt_authentication.jwt_manager');

        return $jwtManager->create($user);
    }

    public function mockRabbitMQProducer($mockProducer)
    {
        $this->getSymfony()->kernel->getContainer()->set('old_sound_rabbit_mq.send_message_producer', $mockProducer);
    }

    public function getSchema(string $fileName): string
    {
        $projectRoot = $this->getSymfony()->kernel->getProjectDir();
        $schemaPath = $projectRoot.self::SCHEMA_FOLDER;

        return str_replace(
            'schemas://',
            sprintf('file://%s', $schemaPath),
            file_get_contents($schemaPath.$fileName)
        );
    }

    private function getSymfony()
    {
        return $this->getModule('Symfony');
    }
}
