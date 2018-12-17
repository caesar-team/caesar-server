<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Model\Event\AppEvents;
use App\Repository\UserRepository;
use App\Services\File\FileDownloader;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseUserProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\TranslatorInterface;

class FOSUBUserProvider extends BaseUserProvider
{
    /** @var EventDispatcher */
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

    public function __construct(
        UserManagerInterface $userManager,
        FileDownloader $downloader,
        EventDispatcherInterface $eventDispatcher,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        array $properties
    ) {
        parent::__construct($userManager, $properties);
        $this->eventDispatcher = $eventDispatcher;
        $this->downloader = $downloader;
        $this->userRepository = $userRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            $user = parent::loadUserByOAuthUserResponse($response);
        } catch (AccountNotLinkedException $e) {
            $user = $this->userManager->findUserByEmail($response->getEmail());

            $this->checkEmailDomain($response->getEmail());
            if ($user instanceof User && $user->isGuest()) {
                throw new AuthenticationException(
                    $this->translator->trans('authentication.user_restriction', ['%email%' => $response->getEmail()])
                );
            }

            if (!$user) {
                /** @var User $user */
                $user = $this->userManager->createUser();
                $user->setEmail($response->getEmail());
                $user->setPlainPassword(md5(uniqid('', true)));
                $user->setUsername($response->getNickname());
                $user->setEnabled(true);

                $avatar = $this->downloader->createAvatarFromLink($response->getProfilePicture());
                $user->setAvatar($avatar);

                $this->userManager->updateCanonicalFields($user);
                $this->userManager->updatePassword($user);

                $this->eventDispatcher->dispatch(AppEvents::REGISTER_BY_GOOGLE, new GenericEvent($user));
            }
        }

        $serviceName = $response->getResourceOwner()->getName();

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue($user, ucfirst($serviceName).'Id', $response->getUsername());

        return $user;
    }

    /**
     * @param string|null $email
     *
     * @throws AuthenticationException
     */
    private function checkEmailDomain(?string $email): void
    {
        preg_match('/(?<=@)(.+)$/', $email, $matches);
        $domain = $matches[1];
        if (!in_array($domain, explode(',', getenv('OAUTH_ALLOWED_DOMAINS')), true)
            && !$this->userRepository->findOneBy(['email' => $email])
        ) {
            throw new AuthenticationException($this->translator->trans('authentication.email_domain_restriction', ['%domain%' => $domain]));
        }
    }
}
