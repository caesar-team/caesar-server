<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190326142921 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE message_history (id UUID NOT NULL, code VARCHAR(50) NOT NULL, recipient_id VARCHAR(255) NOT NULL, category VARCHAR(50) DEFAULT 'email' NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql('CREATE INDEX search_by_recipient_idx ON message_history (recipient_id)');
        $this->addSql("COMMENT ON COLUMN message_history.id IS '(DC2Type:uuid)'");
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('message_history');
    }
}
