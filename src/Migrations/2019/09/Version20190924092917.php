<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190924092917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $teams = $this->connection->fetchAll('SELECT inbox_id, lists_id FROM groups WHERE alias <> ? OR alias IS NULL', ['default']);

        $migrationIds = [];
        foreach ($teams as $team) {
            $defaultListId = $this->connection->fetchColumn('SELECT id FROM directory WHERE parent_list_id = ? AND label = ?', [$team['lists_id'], 'default']);
            $migrationIds[$team['inbox_id']] = $defaultListId;
        }

        foreach ($migrationIds as $inboxId => $defaultId) {
            $this->addSql('UPDATE item SET parent_list_id = ? WHERE parent_list_id = ?', [$defaultId, $inboxId]);
        }
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');
    }
}
