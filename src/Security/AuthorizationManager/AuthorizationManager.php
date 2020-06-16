<?php

declare(strict_types=1);

namespace App\Security\AuthorizationManager;

use App\Entity\User;
use App\Repository\InvitationRepository;
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

    private InvitationRepository $invitationRepository;

    public function __construct(
        UserManagerInterface $userManager,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        InvitationRepository $invitationRepository
    ) {
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->invitationRepository = $invitationRepository;
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
        $invitation = $this->invitationRepository->findOneFreshByHash($hash);

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
        if (!in_array($domain, explode(',', (string) getenv('OAUTH_ALLOWED_DOMAINS')), true)
            && !$userRepository->findOneBy(['email' => $email])
        ) {
            throw new AuthenticationException($this->translator->trans('authentication.email_domain_restriction', ['%domain%' => $domain]));
        }
    }
}
