<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181019092107 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE share');
        $this->addSql('ALTER TABLE post ADD original_post_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN post.original_post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DCD09ADDB FOREIGN KEY (original_post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DCD09ADDB ON post (original_post_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE share (id UUID NOT NULL, user_id UUID DEFAULT NULL, post_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_ef069d5aa76ed395 ON share (user_id)');
        $this->addSql('CREATE INDEX idx_ef069d5a4b89032c ON share (post_id)');
        $this->addSql('COMMENT ON COLUMN share.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN share.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN share.post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT fk_ef069d5aa76ed395 FOREIGN KEY (user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT fk_ef069d5a4b89032c FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DCD09ADDB');
        $this->addSql('DROP INDEX IDX_5A8A6C8DCD09ADDB');
        $this->addSql('ALTER TABLE post DROP original_post_id');
    }
}
