<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class UserDirectory extends AbstractDirectory
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="directories")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
