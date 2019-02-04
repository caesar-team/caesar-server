<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181015131039 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE share (id UUID NOT NULL, user_id UUID DEFAULT NULL, post_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EF069D5AA76ED395 ON share (user_id)');
        $this->addSql('CREATE INDEX IDX_EF069D5A4B89032C ON share (post_id)');
        $this->addSql('COMMENT ON COLUMN share.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN share.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN share.post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5AA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A4B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD last_updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE post RENAME COLUMN label TO name');
        $this->addSql('ALTER TABLE post RENAME COLUMN data TO secret');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE share');
        $this->addSql('ALTER TABLE post DROP last_updated');
        $this->addSql('ALTER TABLE post RENAME COLUMN name TO label');
        $this->addSql('ALTER TABLE post RENAME COLUMN secret TO data');
    }
}
