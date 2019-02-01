<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190201092048 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE fingerprint (id UUID NOT NULL, user_id UUID DEFAULT NULL, string VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FC0B754AA76ED395 ON fingerprint (user_id)');
        $this->addSql('CREATE INDEX idx_fingerprint_string ON fingerprint (string)');
        $this->addSql('COMMENT ON COLUMN fingerprint.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN fingerprint.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN fingerprint.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE fingerprint ADD CONSTRAINT FK_FC0B754AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE fingerprint');
    }
}
