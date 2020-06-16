<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190822095120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item ADD owner_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN item.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F1B251E7E3C61F9 ON item (owner_id)');

        $users = $this->connection->fetchAll('SELECT id, inbox_id, lists_id, trash_id FROM fos_user');
        foreach ($users as $user) {
            $lists = $this->connection->fetchAll('SELECT id FROM directory WHERE parent_list_id = ?', [$user['lists_id']]);
            $lists = array_column($lists, 'id');
            array_push($lists, $user['inbox_id']);
            array_push($lists, $user['trash_id']);
            $items = $this->connection->fetchAll('SELECT id FROM item WHERE parent_list_id IN (?)', [$lists], [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]);
            $items = array_column($items, 'id');

            $this->addSql('UPDATE item SET owner_id=? WHERE id IN (?)', [$user['id'], $items], [null, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E7E3C61F9');
        $this->addSql('DROP INDEX IDX_1F1B251E7E3C61F9');
        $this->addSql('ALTER TABLE item DROP owner_id');
    }
}
