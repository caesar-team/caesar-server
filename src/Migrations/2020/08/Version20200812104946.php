<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200812104946 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item ADD related_item_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN item.related_item_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E2D7698FB FOREIGN KEY (related_item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F1B251E2D7698FB ON item (related_item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E2D7698FB');
        $this->addSql('DROP INDEX IDX_1F1B251E2D7698FB');
        $this->addSql('ALTER TABLE item DROP related_item_id');
    }
}
