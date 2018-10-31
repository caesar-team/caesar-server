<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseController;
use FOS\UserBundle\Doctrine\UserManager;

/**
 * Class AdminController.
 */
class UserController extends BaseController
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function createNewUserEntity(): User
    {
        $user = $this->userManager->createUser();
        $user->setPlainPassword(md5(uniqid('', true)));

        return $user;
    }
}
