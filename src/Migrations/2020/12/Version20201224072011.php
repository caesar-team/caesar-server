<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201224072011 extends AbstractMigration
{
    private const TYPES = [
        'default' => 'default',
        'lists' => 'root',
        'trash' => 'trash',
        'inbox' => 'inbox',
    ];

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP CONSTRAINT directory_type_check');
        $this->addSql('UPDATE directory SET type = :type', ['type' => 'list']);
        foreach (self::TYPES as $label => $type) {
            $this->addSql('UPDATE directory SET type = :type WHERE label = :label', [
                'type' => $type,
                'label' => $label,
            ]);
        }

        $this->addSql('ALTER TABLE directory ADD CONSTRAINT directory_type_check CHECK (type IN (\'default\', \'root\', \'list\', \'inbox\', \'trash\'))');
        $this->addSql('COMMENT ON COLUMN directory.type IS \'(DC2Type:DirectoryEnumType)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP CONSTRAINT directory_type_check');
        $this->addSql('UPDATE directory SET type = :type WHERE type IN (:types)',
            [
                'type' => 'list',
                'types' => ['default', 'root', 'inbox'],
            ],
            [
                'type' => \PDO::PARAM_STR,
                'types' => Connection::PARAM_STR_ARRAY,
            ],
        );
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT directory_type_check CHECK (type IN (\'list\', \'inbox\', \'trash\', \'credentials\', \'system\', \'keypair\'))');
        $this->addSql('COMMENT ON COLUMN directory.type IS \'(DC2Type:NodeEnumType)\'');
    }
}
