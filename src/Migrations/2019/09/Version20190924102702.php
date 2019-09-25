<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190924102702 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE groups DROP CONSTRAINT fk_f06d397018da89dd');
        $this->addSql('DROP INDEX uniq_f06d397018da89dd');
        $this->addSql('ALTER TABLE groups DROP inbox_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE groups ADD inbox_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN groups.inbox_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT fk_f06d397018da89dd FOREIGN KEY (inbox_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_f06d397018da89dd ON groups (inbox_id)');
    }
}
