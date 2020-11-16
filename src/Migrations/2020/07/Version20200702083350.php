<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200702083350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory ADD team_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN directory.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DA296CD8AE FOREIGN KEY (team_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_467844DA296CD8AE ON directory (team_id)');
        
        $this->addSql('UPDATE directory SET team_id = groups.id FROM groups WHERE groups.trash_id IS NOT NULL AND directory.id = groups.trash_id');
        $this->addSql('UPDATE directory SET team_id = groups.id FROM groups WHERE groups.lists_id IS NOT NULL AND directory.id = groups.lists_id');
        $this->addSql('UPDATE directory SET team_id = groups.id FROM groups WHERE directory.parent_list_id = groups.lists_id');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_467844DA296CD8AE');
        $this->addSql('DROP INDEX IDX_467844DA296CD8AE');
        $this->addSql('ALTER TABLE directory DROP team_id');
    }
}
