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

        return file_get_contents($projectRoot . self::SCHEMA_FOLDER . $fileName);
    }

    private function getSymfony()
    {
        return $this->getModule('Symfony');
    }
}
