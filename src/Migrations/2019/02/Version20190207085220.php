<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190207085220 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE link (id UUID NOT NULL, parent_item_id UUID DEFAULT NULL, guest_user_id UUID DEFAULT NULL, data VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36AC99F160272618 ON link (parent_item_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36AC99F1E7AB17D9 ON link (guest_user_id)');
        $this->addSql('COMMENT ON COLUMN link.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN link.parent_item_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN link.guest_user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F160272618 FOREIGN KEY (parent_item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F1E7AB17D9 FOREIGN KEY (guest_user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE link');
    }
}
