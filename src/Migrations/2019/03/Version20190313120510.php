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
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema): void
    {
        $itemMasktable = $schema->getTable('item_mask');
        $itemMasktable->dropColumn('last_updated');

        $this->addSql('CREATE TABLE invitation (id UUID NOT NULL, hash TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F11D61A2D1B862B8 ON invitation (hash)');
        $this->addSql("COMMENT ON COLUMN invitation.id IS '(DC2Type:uuid)'");
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema): void
    {
        $itemMasktable = $schema->getTable('item_mask');
        $itemMasktable->addColumn('last_updated', 'datetime');
    }
}
