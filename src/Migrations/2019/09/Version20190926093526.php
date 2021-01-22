<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190926093526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $teams = $this->connection->fetchAll('SELECT id, lists_id, trash_id FROM groups WHERE lists_id IS NULL OR trash_id IS NULL');
        foreach ($teams as $team) {
            $rootId = Uuid::uuid4();
            $defaultId = Uuid::uuid4();
            $trashId = Uuid::uuid4();

            $this->addSql(
                'INSERT INTO directory (id, parent_list_id, label, type, sort) VALUES (?,?,?,?,?)',
                [ $rootId,  null,  'lists',  'list', 0 ]
            );
            $this->addSql('UPDATE groups SET lists_id = ? WHERE id = ?', [$rootId, $team['id']]);

            $this->addSql(
                'INSERT INTO directory (id, parent_list_id, label, type, sort) VALUES (?,?,?,?,?)',
                [ $defaultId, $rootId, 'default', 'list', 0 ]
            );

            $this->addSql(
                'INSERT INTO directory (id, parent_list_id, label, type, sort) VALUES (?,?,?,?,?)',
                [ $trashId, null, 'trash', 'trash', 1 ]
            );
            $this->addSql('UPDATE groups SET trash_id = ? WHERE id = ?', [$trashId, $team['id']]);
        }
    }

    public function down(Schema $schema): void
    {
        $itShouldntChanges = true;
        $this->skipIf($itShouldntChanges, 'It shouldn\'t changes');
    }
}
