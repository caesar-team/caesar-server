<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201008120855 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE item_update');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE item_update (id UUID NOT NULL, item_id UUID DEFAULT NULL, updated_by_id UUID DEFAULT NULL, secret TEXT NOT NULL, last_updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_1edc40b7896dbbde ON item_update (updated_by_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_1edc40b7126f525e ON item_update (item_id)');
        $this->addSql('COMMENT ON COLUMN item_update.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN item_update.item_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN item_update.updated_by_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE item_update ADD CONSTRAINT fk_1edc40b7896dbbde FOREIGN KEY (updated_by_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item_update ADD CONSTRAINT fk_1edc40b7126f525e FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
