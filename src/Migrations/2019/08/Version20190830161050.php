<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190830161050 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE groups ADD inbox_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE groups ADD lists_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE groups ADD trash_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN groups.inbox_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN groups.lists_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN groups.trash_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D397018DA89DD FOREIGN KEY (inbox_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D39709D26499B FOREIGN KEY (lists_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT FK_F06D39702C87042F FOREIGN KEY (trash_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F06D397018DA89DD ON groups (inbox_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F06D39709D26499B ON groups (lists_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F06D39702C87042F ON groups (trash_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE groups DROP CONSTRAINT FK_F06D397018DA89DD');
        $this->addSql('ALTER TABLE groups DROP CONSTRAINT FK_F06D39709D26499B');
        $this->addSql('ALTER TABLE groups DROP CONSTRAINT FK_F06D39702C87042F');
        $this->addSql('DROP INDEX UNIQ_F06D397018DA89DD');
        $this->addSql('DROP INDEX UNIQ_F06D39709D26499B');
        $this->addSql('DROP INDEX UNIQ_F06D39702C87042F');
        $this->addSql('ALTER TABLE groups DROP inbox_id');
        $this->addSql('ALTER TABLE groups DROP lists_id');
        $this->addSql('ALTER TABLE groups DROP trash_id');
    }
}
