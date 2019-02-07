<?php

declare(strict_types=1);

namespace App\Services;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\Link;
use App\Entity\User;
use App\Model\Request\LinkCreateRequest;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Ramsey\Uuid\Uuid;

class LinkManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserManagerInterface
     */
    private $fosUserManager;

    public function __construct(EntityManagerInterface $entityManager, UserManagerInterface $fosUserManager)
    {
        $this->entityManager = $entityManager;
        $this->fosUserManager = $fosUserManager;
    }

    public function create(LinkCreateRequest $linkRequest): Link
    {
        $id = Uuid::uuid4()->toString();

        $guest = new User();
        $guest->setEmail($id);
        $guest->setUsername($id);
        $guest->setGuest(true);
        $guest->setEnabled(true);
        $guest->setPassword($id);
        $guest->setGoogleAuthenticatorSecret($id);
        $guest->setPublicKey($linkRequest->getPublicKey());
        $guest->setEncryptedPrivateKey($linkRequest->getEncryptedPrivateKey());
        $this->fosUserManager->updateUser($guest);

        $parentItem = $linkRequest->getItem();

        $item = new Item();
        $item->setAccess(AccessEnumType::TYPE_READ);
        $item->setSecret($linkRequest->getSecret());
        $item->setType($parentItem->getType());
        $item->setParentList($guest->getInbox());

        $link = new Link($guest, $parentItem);

        $this->entityManager->persist($item);
        $this->entityManager->persist($link);
        $this->entityManager->flush();

        return $link;
    }
}
