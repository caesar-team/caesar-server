<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181128113704 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE audit_events (id UUID NOT NULL, target_id UUID DEFAULT NULL, blame VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, message TEXT NOT NULL, ip VARCHAR(255) DEFAULT NULL, verify BOOLEAN DEFAULT \'false\' NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_70597B91158E0B66 ON audit_events (target_id)');
        $this->addSql('COMMENT ON COLUMN audit_events.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN audit_events.target_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE audit_events ADD CONSTRAINT FK_70597B91158E0B66 FOREIGN KEY (target_id) REFERENCES post (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE audit_events');
    }
}
