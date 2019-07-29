<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190729132141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("ALTER TABLE item ADD name VARCHAR(255) DEFAULT 'unknown' NOT NULL");
        $this->addSql("ALTER TABLE item_update ADD name VARCHAR(255) DEFAULT 'unknown' NOT NULL");

    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('item');
        $table->dropColumn('name');
        $table = $schema->getTable('item_update');
        $table->dropColumn('name');
    }
}
