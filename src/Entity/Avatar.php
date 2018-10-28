<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\File\AbstractImage;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Event\EntityListener\FileListener"})
 */
class Avatar extends AbstractImage
{
    /**
     * @var User
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\User",
     *     inversedBy="avatar",
     * )
     */
    protected $user;

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->getFilePath();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    protected function getSubDir(): string
    {
        return 'user';
    }
}
