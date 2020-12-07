<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Event\User\RegistrationCompletedEvent;
use App\Repository\UserRepository;
use App\Security\AuthorizationManager\AuthorizationManager;
use App\Services\File\FileDownloader;
use App\Services\TeamManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseUserProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class FOSUBUserProvider extends BaseUserProvider
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var FileDownloader */
    private $downloader;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var TeamManager
     */
    private $groupManager;
    /**
     * @var AuthorizationManager
     */
    private $authorizationManager;

    public function __construct(
        UserManagerInterface $userManager,
        FileDownloader $downloader,
        EventDispatcherInterface $eventDispatcher,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        array $properties,
        TeamManager $groupManager,
        AuthorizationManager $authorizationManager
    ) {
        parent::__construct($userManager, $properties);
        $this->eventDispatcher = $eventDispatcher;
        $this->downloader = $downloader;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
        $this->groupManager = $groupManager;
        $this->authorizationManager = $authorizationManager;
    }

    /**
     * @psalm-suppress InvalidReturnType
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $this->checkEmailDomain($response->getEmail());

        try {
            $user = parent::loadUserByOAuthUserResponse($response);
        } catch (AccountNotLinkedException $e) {
            $user = $this->userManager->findUserByEmail($response->getEmail());

            $this->denyAccessUnlessGranted($response, $user);

            if (!$user) {
                $user = $this->userManager->createUser();
                $user->setEmail($response->getEmail());
                $user->setPlainPassword(md5(uniqid('', true)));
                $user->setUsername($response->getEmail());
                $user->setEnabled(true);
                if ($user instanceof User) {
                    $avatar = $this->downloader->createAvatarFromLink($response->getProfilePicture());
                    $user->setAvatar($avatar);
                }

                $this->userManager->updateCanonicalFields($user);
                $this->userManager->updatePassword($user);

                if ($user instanceof  User) {
                    $this->eventDispatcher->dispatch(
                        new RegistrationCompletedEvent($user, RegistrationCompletedEvent::FROM_GOOGLE)
                    );
                }
            }
        }

        $serviceName = $response->getResourceOwner()->getName();

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($user, ucfirst($serviceName).'Id', $response->getUsername());

        /** @psalm-suppress InvalidReturnStatement */
        return $user;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function denyAccessUnlessGranted(UserResponseInterface $response, UserInterface $user = null): void
    {
        if (!$user instanceof User) {
            return;
        }

        $email = $response->getEmail();
        if ($this->authorizationManager->hasInvitation($user)) {
            $errorMessage = $this->translator->trans('authentication.invitation_wrong_auth_point', ['%email%' => $email]);
            $error = [
                'code' => AuthorizationManager::ERROR_UNFINISHED_FLOW_USER,
                'description' => $errorMessage,
            ];

            throw new AccessDeniedHttpException(json_encode($error), null, Response::HTTP_BAD_REQUEST);
        }

        $this->authorizationManager->checkEmailDomain($email);

        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            throw new AuthenticationException($this->translator->trans('authentication.user_restriction', ['%email%' => $email]));
        }
    }

    /**
     * @throws AuthenticationException
     */
    private function checkEmailDomain(?string $email): void
    {
        preg_match('/(?<=@)(.+)$/', $email, $matches);
        $domain = $matches[1];
        if (!in_array($domain, explode(',', (string) getenv('OAUTH_ALLOWED_DOMAINS')), true)) {
            throw new AuthenticationException($this->translator->trans('authentication.email_domain_restriction', ['%domain%' => $domain]));
        }
    }
}
