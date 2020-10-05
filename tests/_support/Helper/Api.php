<?php

namespace App\Tests\Helper;

use App\Entity\User;
use App\Security\BackupCodes\BackupCodesEncoderInterface;
use Codeception\Module\Symfony;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class Api extends \Codeception\Module
{
    private const SCHEMA_FOLDER = '/tests/_support/schemas/';

    public function getToken(UserInterface $user): string
    {
        $jwtManager = $this->getSymfony()->grabService('lexik_jwt_authentication.jwt_manager');

        return $jwtManager->create($user);
    }

    public function encodeCode(string $code): string
    {
        /** @var BackupCodesEncoderInterface $encoder */
        $encoder = $this->getSymfony()->grabService(BackupCodesEncoderInterface::class);

        return $encoder->encode([$code])[0];
    }

    public function generateCsrf(string $tokenId)
    {
        /** @var CsrfTokenManagerInterface $tokenManager */
        $tokenManager = $this->getSymfony()->grabService('security.csrf.token_manager');

        $token = $tokenManager->getToken($tokenId)->getValue();

        /** @var Session $session */
        $session = $this->getSymfony()->grabService('session');
        $session->save();

        return $token;
    }

    public function symfonyAuth(User $user): void
    {
        $symfony = $this->getSymfony();

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $symfony->grabService('security.token_storage')->setToken($token);

        /** @var Session $session */
        $session = $symfony->grabService('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $symfony->client->getCookieJar()->set($cookie);
    }

    public function deleteFromAdmin(string $controller, string $uid)
    {
        $crudId = substr(sha1(getenv('APP_SECRET').$controller), 0, 7);

        $this->symfonyRequest(
            'DELETE',
            sprintf('/admin?crudAction=delete&entityId=%s&crudId=%s', $uid, $crudId),
            ['_method' => 'DELETE', 'delete_form' => ['_easyadmin_delete_flag' => 1], 'token' => $this->generateCsrf('ea-delete')]
        );
    }

    public function symfonyRequest(string $method, string $url, array $params = [])
    {
        return $this->getSymfony()->_request($method, $url, $params);
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

    /**
     * @return Symfony
     */
    private function getSymfony()
    {
        return $this->getModule('Symfony');
    }
}
