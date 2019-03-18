<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190318115912 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema) : void
    {
        $itemTable = $schema->getTable('item');
        $itemTable->addColumn('cause', 'string', [
            'length' => 10,
            'notnull' => false,
            'default' => 'invite',
        ]);
        $itemTable->addColumn('link', 'string', [
            'length' => 510,
            'notnull' => false,
        ]);


        $itemMaskTable = $schema->getTable('item_mask');
        $itemMaskTable->addColumn('link', 'string', [
            'length' => 510,
            'notnull' => false,
        ]);
        $itemMaskTable->addColumn('cause', 'string', [
            'length' => 10,
            'notnull' => false,
            'default' => 'invite',
        ]);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema) : void
    {
        $itemTable = $schema->getTable('item');
        $itemTable->dropColumn('cause');
        $itemTable->dropColumn('link');
        $itemMaskTable = $schema->getTable('item_mask');
        $itemMaskTable->dropColumn('cause');
        $itemMaskTable->dropColumn('link');
    }
}
