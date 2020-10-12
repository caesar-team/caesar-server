<?php

declare(strict_types=1);

namespace App\EventSubscriber\Doctrine;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * Class MigrationEventSubscriber.
 *
 * @see https://github.com/doctrine/dbal/issues/1110#issuecomment-255765189
 */
class MigrationEventSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postGenerateSchema',
        ];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schema = $args->getSchema();

        if (!$schema->hasNamespace('public')) {
            $schema->createNamespace('public');
        }
    }
}
