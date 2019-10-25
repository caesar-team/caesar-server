<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191025102535 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item ADD team_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN item.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E296CD8AE FOREIGN KEY (team_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F1B251E296CD8AE ON item (team_id)');

        $teams = $this->connection->fetchAll("SELECT id, trash_id, lists_id FROM groups");
        foreach ($teams as $team) {
            $this->assignTeamToItems($team);
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E296CD8AE');
        $this->addSql('DROP INDEX IDX_1F1B251E296CD8AE');
        $this->addSql('ALTER TABLE item DROP team_id');
    }

    private function assignTeamToItems(array $team)
    {
        $items = $this->connection->fetchAll("SELECT i.id FROM item i WHERE i.parent_list_id IN(SELECT id FROM directory d WHERE d.parent_list_id = ?) OR i.parent_list_id = ?", [
            $team['lists_id'],
            $team['trash_id'],
        ]);

        foreach ($items as $item) {
            $this->addSql("UPDATE item SET team_id = ? WHERE id = ?", [$team['id'], $item['id']]);
        }
    }
}
