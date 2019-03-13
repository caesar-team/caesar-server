<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190313120510 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema) : void
    {
        $itemMasktable = $schema->getTable('item_mask');
        $itemMasktable->dropColumn('last_updated');
        $userTable = $schema->getTable('fos_user');
        $userTable->addColumn('invitation', 'boolean', [
            'notnull' => true,
            'default' => false
        ]);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema) : void
    {
        $userTable = $schema->getTable('fos_user');
        $userTable->dropColumn('invitation');
        $itemMasktable = $schema->getTable('item_mask');
        $itemMasktable->addColumn('last_updated', 'datetime');
    }
}
