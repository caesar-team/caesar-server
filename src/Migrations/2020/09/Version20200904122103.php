<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200904122103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DELETE FROM fingerprint');
        $this->addSql('DROP INDEX idx_fingerprint_string');
        $this->addSql('ALTER TABLE fingerprint ADD client VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fingerprint ADD last_ip VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fingerprint ADD expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE fingerprint RENAME COLUMN string TO fingerprint');
        $this->addSql('COMMENT ON COLUMN fingerprint.expired_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE INDEX idx_fingerprint_string ON fingerprint (fingerprint)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP INDEX idx_fingerprint_string');
        $this->addSql('ALTER TABLE fingerprint DROP client');
        $this->addSql('ALTER TABLE fingerprint DROP last_ip');
        $this->addSql('ALTER TABLE fingerprint DROP expired_at');
        $this->addSql('ALTER TABLE fingerprint RENAME COLUMN fingerprint TO string');
        $this->addSql('CREATE INDEX idx_fingerprint_string ON fingerprint (string)');
    }
}
