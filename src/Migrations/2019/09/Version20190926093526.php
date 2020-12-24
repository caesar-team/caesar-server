<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Directory;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

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
            $root = Directory::createRootList();
            $default = Directory::createDefaultList();
            $default->setParentList($root);
            $trash = Directory::createTrash();

            $this->addSql(
                'INSERT INTO directory (id, parent_list_id, label, type, sort) VALUES (?,?,?,?,?)',
                [
                    $root->getId()->toString(),
                    null,
                    $root->getLabel(),
                    'list',
                    $root->getSort(),
                ]
            );
            $this->addSql('UPDATE groups SET lists_id = ? WHERE id = ?', [$root->getId()->toString(), $team['id']]);

            $this->addSql(
                'INSERT INTO directory (id, parent_list_id, label, type, sort) VALUES (?,?,?,?,?)',
                [
                    $default->getId()->toString(),
                    $default->getParentList()->getId()->toString(),
                    $default->getLabel(),
                    'list',
                    $default->getSort(),
                ]
            );

            $this->addSql(
                'INSERT INTO directory (id, parent_list_id, label, type, sort) VALUES (?,?,?,?,?)',
                [
                    $trash->getId()->toString(),
                    null,
                    $trash->getLabel(),
                    'trash',
                    $trash->getSort(),
                ]
            );
            $this->addSql('UPDATE groups SET trash_id = ? WHERE id = ?', [$trash->getId()->toString(), $team['id']]);
        }
    }

    public function down(Schema $schema): void
    {
        $itShouldntChanges = true;
        $this->skipIf($itShouldntChanges, 'It shouldn\'t changes');
    }
}
