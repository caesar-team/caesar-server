<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200722114245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE message_log (id UUID NOT NULL, event VARCHAR(50) NOT NULL, recipient VARCHAR(255) NOT NULL, deferred BOOLEAN DEFAULT \'false\' NOT NULL, options TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX message_log_recipient_idx ON message_log (recipient)');
        $this->addSql('COMMENT ON COLUMN message_log.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message_log.options IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN message_log.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN message_log.sent_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE message_log');
    }
}
