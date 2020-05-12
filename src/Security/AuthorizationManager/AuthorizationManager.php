<?php

declare(strict_types=1);

namespace App\Security\AuthorizationManager;

use App\Entity\Security\Invitation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthorizationManager
{
    public const ERROR_UNFINISHED_FLOW_USER = 'ERROR_UNFINISHED_FLOW_USER';
    /**
     * @var UserManagerInterface
     */
    private $userManager;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        UserManagerInterface $userManager,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserByInvitation(string $email): ?UserInterface
    {
        $user = $this->userManager->findUserByEmail($email);
        if (!$this->hasInvitation($user)) {
            return null;
        }

        return $user;
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function hasInvitation(UserInterface $user): bool
    {
        $hash = (InvitationEncoder::initEncoder())->encode($user->getEmail());
        $invitation = $this->entityManager->getRepository(Invitation::class)->findOneFreshByHash($hash);

        if ($invitation) {
            return true;
        }

        return false;
    }

    public function checkEmailDomain(?string $email): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        preg_match('/(?<=@)(.+)$/', $email, $matches);
        $domain = $matches[1];
        if (!in_array($domain, explode(',', getenv('OAUTH_ALLOWED_DOMAINS')), true)
            && !$userRepository->findOneBy(['email' => $email])
        ) {
            throw new AuthenticationException($this->translator->trans('authentication.email_domain_restriction', ['%domain%' => $domain]));
        }
    }
}
