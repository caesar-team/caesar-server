<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

/**
 * Class MigrationEventSubscriber.
 *
 * @see https://github.com/doctrine/dbal/issues/1110#issuecomment-255765189
 */
class MigrationEventSubscriber implements EventSubscriber
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

    /**
     * @param GenerateSchemaEventArgs $args
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        $schema = $args->getSchema();

        if (!$schema->hasNamespace('public')) {
            $schema->createNamespace('public');
        }
    }
}
