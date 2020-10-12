<?php

declare(strict_types=1);

namespace App\Request\Key;

use Doctrine\Common\Collections\ArrayCollection;

class PublicKeysRequest
{
    /**
     * @var ArrayCollection
     */
    private $emails;

    public function __construct()
    {
        $this->emails = new ArrayCollection();
    }

    public function getEmails(): array
    {
        return $this->emails->toArray();
    }

    public function addEmail(string $email): void
    {
        $this->emails->add($email);
    }

    public function removeEmail(string $email): void
    {
        $this->emails->removeElement($email);
    }
}
