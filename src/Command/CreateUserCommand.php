<?php

namespace App\Command;

use App\Entity\Srp;
use App\Entity\User;
use App\Event\User\RegistrationCompletedEvent;
use App\Services\SrpHandler;
use FOS\UserBundle\Command\CreateUserCommand as BaseCreateUserCommand;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\UserManipulator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateUserCommand extends BaseCreateUserCommand
{
    protected static $defaultName = 'app:user:create';

    private UserManipulator $userManipulator;

    private UserManagerInterface $userManager;

    private SrpHandler $srpHandler;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        UserManipulator $userManipulator,
        UserManagerInterface $userManager,
        SrpHandler $srpHandler,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($userManipulator);

        $this->userManipulator = $userManipulator;
        $this->userManager = $userManager;
        $this->srpHandler = $srpHandler;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @psalm-suppress PossiblyInvalidCast */
        $username = (string) $input->getArgument('username');
        /** @psalm-suppress PossiblyInvalidCast */
        $email = (string) $input->getArgument('email');
        /** @psalm-suppress PossiblyInvalidCast */
        $password = (string) $input->getArgument('password');
        $inactive = $input->getOption('inactive');
        $superadmin = $input->getOption('super-admin');

        $user = new User(new Srp());
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((bool) !$inactive);
        if ($superadmin) {
            $user->addRole(User::ROLE_SUPER_ADMIN);
            $user->addRole(User::ROLE_ADMIN);
        }
        $seed = $this->srpHandler->getRandomSeed();
        $user->getSrp()->setSeed($seed);
        $x = $this->srpHandler->generateX($seed, $username, $password);
        $verifier = $this->srpHandler->generateVerifier($x);
        $user->getSrp()->setVerifier($verifier);
        $this->userManager->updateUser($user);

        $this->eventDispatcher->dispatch(new RegistrationCompletedEvent($user));

        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));

        return 0;
    }
}
