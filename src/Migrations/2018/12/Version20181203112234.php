<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181203112234 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE post_tags (post_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(post_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_A6E9F32D4B89032C ON post_tags (post_id)');
        $this->addSql('CREATE INDEX IDX_A6E9F32DBAD26311 ON post_tags (tag_id)');
        $this->addSql('COMMENT ON COLUMN post_tags.post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN post_tags.tag_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE tags (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_tags_name ON tags (name)');
        $this->addSql('COMMENT ON COLUMN tags.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE post_tags ADD CONSTRAINT FK_A6E9F32D4B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post_tags ADD CONSTRAINT FK_A6E9F32DBAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE post_tags DROP CONSTRAINT FK_A6E9F32DBAD26311');
        $this->addSql('DROP TABLE post_tags');
        $this->addSql('DROP TABLE tags');
    }
}
