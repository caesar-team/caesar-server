<?php

namespace App\Command;

use App\Entity\Srp;
use App\Entity\User;
use App\Services\TeamManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\UserBundle\Util\UserManipulator;
use FOS\UserBundle\Command\CreateUserCommand as BaseCreateUserCommand;
use App\Services\SrpHandler;

class CreateUserCommand extends BaseCreateUserCommand
{
    protected static $defaultName = 'app:user:create';

    /**
     * @var UserManipulator
     */
    private $userManipulator;
    /**
     * @var UserManagerInterface
     */
    private $userManager;
    /**
     * @var TeamManager
     */
    private $teamManager;
    /**
     * @var SrpHandler
     */
    private $srpHandler;

    public function __construct(
        UserManipulator $userManipulator,
        UserManagerInterface $userManager,
        TeamManager $teamManager,
        SrpHandler $srpHandler
    )
    {
        parent::__construct($userManipulator);

        $this->userManipulator = $userManipulator;
        $this->userManager = $userManager;
        $this->teamManager = $teamManager;
        $this->srpHandler = $srpHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $inactive = $input->getOption('inactive');
        $superadmin = $input->getOption('super-admin');

        $user = new User(new Srp());
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setEnabled((bool) !$inactive);
        $user->setSuperAdmin((bool) $superadmin);
        $seed = $this->srpHandler->getRandomSeed();
        $user->getSrp()->setSeed($seed);
        $x = $this->srpHandler->generateX($seed, $username, $password);
        $verifier = $this->srpHandler->generateVerifier($x);
        $user->getSrp()->setVerifier($verifier);
        $this->userManager->updateUser($user);

        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));
    }
}
