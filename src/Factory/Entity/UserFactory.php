<?php

declare(strict_types=1);

namespace App\Factory\Entity;

use App\Entity\Srp;
use App\Entity\User;
use App\Factory\Entity\Directory\UserDirectoryFactory;
use App\Repository\UserRepository;
use App\Request\Srp\RegistrationRequest;
use App\Request\SrpAwareRequestInterface;
use App\Request\User\CreateInvitedUserRequest;
use App\Security\AuthorizationManager\AuthorizationManager;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserFactory
{
    private UserDirectoryFactory $directoryFactory;

    private UserRepository $repository;

    private UserManagerInterface $userManager;

    private AuthorizationManager $authorizationManager;

    private TranslatorInterface $translator;

    private TokenGeneratorInterface $tokenGenerator;

    public function __construct(
        UserDirectoryFactory $directoryFactory,
        UserRepository $repository,
        UserManagerInterface $userManager,
        AuthorizationManager $authorizationManager,
        TranslatorInterface $translator,
        TokenGeneratorInterface $tokenGenerator
    ) {
        $this->directoryFactory = $directoryFactory;
        $this->repository = $repository;
        $this->userManager = $userManager;
        $this->authorizationManager = $authorizationManager;
        $this->translator = $translator;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function create(): User
    {
        $user = new User();
        foreach ($this->directoryFactory->createDefaultDirectories($user) as $directory) {
            $user->addDirectory($directory);
        }

        return $user;
    }

    public function createFromRegistrationRequest(RegistrationRequest $request): User
    {
        $user = $this->userManager->findUserByEmail($request->getEmail());
        if ($user instanceof User && $this->authorizationManager->hasInvitation($user)) {
            throw new AccessDeniedHttpException($this->translator->trans('authentication.invitation_wrong_auth_point', ['%email%' => $request->getEmail()]));
        }

        $user = new User();
        $user->setConfirmationToken($this->tokenGenerator->generateToken());
        $user->setEmail($request->getEmail());
        $user->setUsername($request->getEmail());
        $user->setPlainPassword(uniqid());
        $user->setEnabled(false);
        foreach ($this->directoryFactory->createDefaultDirectories($user) as $directory) {
            $user->addDirectory($directory);
        }

        $this->setSrp($user, $request);

        return $user;
    }

    public function createFromInvitedRequest(CreateInvitedUserRequest $request): User
    {
        $user = $this->repository->findWithoutPublicKey([
            'email' => $request->getEmail(),
        ]);

        if (null === $user) {
            $user = new User(new Srp());
            foreach ($this->directoryFactory->createDefaultDirectories($user) as $directory) {
                $user->addDirectory($directory);
            }
        }
        $user->setEmail($request->getEmail());
        $user->setUsername($request->getEmail());
        $user->setEnabled(true);
        $user->setPlainPassword($request->getPlainPassword());
        $user->setEncryptedPrivateKey($request->getEncryptedPrivateKey());
        $user->setPublicKey($request->getPublicKey());
        $user->setRoles($request->getDomainRoles());
        if ($user->hasRole(User::ROLE_READ_ONLY_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_CHANGE_PASSWORD);
        }
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        }

        $this->setSrp($user, $request);

        return $user;
    }

    private function setSrp(User $user, SrpAwareRequestInterface $request): void
    {
        $srp = $user->getSrp() ?? new Srp();
        $srp->setSeed($request->getSeed());
        $srp->setVerifier($request->getVerifier());

        $user->setSrp($srp);
    }
}
