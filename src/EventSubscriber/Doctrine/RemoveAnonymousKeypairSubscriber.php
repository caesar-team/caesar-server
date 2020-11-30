<?php

declare(strict_types=1);

namespace App\EventSubscriber\Doctrine;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

final class RemoveAnonymousKeypairSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $item = $args->getObject();
        if (!$item instanceof Item
            || NodeEnumType::TYPE_KEYPAIR !== $item->getType()
            || !$item->getSignedOwner()->hasRole(User::ROLE_ANONYMOUS_USER)
        ) {
            return;
        }

        $args->getObjectManager()->remove($item->getSignedOwner());
    }
}
