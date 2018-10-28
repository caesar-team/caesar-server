<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181012123429 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE directory (id UUID NOT NULL, parent_list_id UUID DEFAULT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_467844DA4FE662CD ON directory (parent_list_id)');
        $this->addSql('COMMENT ON COLUMN directory.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.parent_list_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE post (id UUID NOT NULL, parent_list_id UUID NOT NULL, label VARCHAR(255) NOT NULL, data JSONB NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D4FE662CD ON post (parent_list_id)');
        $this->addSql('COMMENT ON COLUMN post.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN post.parent_list_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DA4FE662CD FOREIGN KEY (parent_list_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D4FE662CD FOREIGN KEY (parent_list_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD inbox_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user ADD lists_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user ADD trash_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN fos_user.inbox_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN fos_user.lists_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN fos_user.trash_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A647918DA89DD FOREIGN KEY (inbox_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64799D26499B FOREIGN KEY (lists_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64792C87042F FOREIGN KEY (trash_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A647918DA89DD ON fos_user (inbox_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A64799D26499B ON fos_user (lists_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A64792C87042F ON fos_user (trash_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_467844DA4FE662CD');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D4FE662CD');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A647918DA89DD');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A64799D26499B');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A64792C87042F');
        $this->addSql('DROP TABLE directory');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP INDEX UNIQ_957A647918DA89DD');
        $this->addSql('DROP INDEX UNIQ_957A64799D26499B');
        $this->addSql('DROP INDEX UNIQ_957A64792C87042F');
        $this->addSql('ALTER TABLE fos_user DROP inbox_id');
        $this->addSql('ALTER TABLE fos_user DROP lists_id');
        $this->addSql('ALTER TABLE fos_user DROP trash_id');
    }
}
