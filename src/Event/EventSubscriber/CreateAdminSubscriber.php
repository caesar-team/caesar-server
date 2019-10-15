<?php

declare(strict_types=1);


namespace App\Event\EventSubscriber;


use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CreateAdminSubscriber implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof User) {
            return;
        }

        if (getenv('DOMAIN_ADMIN_EMAIL') === $object->getEmail()) {
            $object->addRole(User::ROLE_SUPER_ADMIN);
        }
    }
}