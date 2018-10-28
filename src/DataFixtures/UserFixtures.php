<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Avatar;
use App\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserFixtures extends AbstractFixture implements FixtureInterface
{
    public function loadProd(ObjectManager $manager)
    {
        $users['ipopov'] = $this->createAdmin('ipopov', 'https://lh3.googleusercontent.com/-yMDv1pu362c/AAAAAAAAAAI/AAAAAAAAAAA/ABtNlbBsSjraSBx4NHkpxDl0w-DR9UPcdw/s32-c-mo/photo.jpg');
        $users['dspiridonov'] = $this->createAdmin('dspiridonov');

        $this->save($users, User::class);
    }

    private function createAdmin(string $name, string $uri = null): User
    {
        $user = new User();
        $user->setUsername($name);
        $user->setEmail("$name@4xxi.com");
        $user->setSuperAdmin(true);
        $user->setEnabled(true);
        $user->setPlainPassword(uniqid('pass_', true));
        if (null !== $uri) {
            $this->addImage($user, $uri);
        }

        return $user;
    }

    private function addImage(User $user, string $uri)
    {
        $filePath = \sprintf('%s/%s', sys_get_temp_dir(), uniqid('file_', true));
        $result = copy($uri, $filePath);

        if (!$result) {
            throw new BadRequestHttpException('Can not download image');
        }

        $user->setAvatar(new Avatar());
        $user->getAvatar()->setFile(new File($filePath));
    }
}
