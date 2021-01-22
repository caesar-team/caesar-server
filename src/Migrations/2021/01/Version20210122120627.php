<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210122120627 extends AbstractMigration
{
    private const DEFAULT_CONFIG = [
        'max_item_size' => [
            'name' => 'Maximum item size',
            'value' => null,
        ],
        'max_import_size' => [
            'name' => 'Maximum import file size',
            'value' => null,
        ],
        'max_avatar_size' => [
            'name' => 'Maximum avatar image size',
            'value' => null,
        ],
    ];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE config (id UUID NOT NULL, name VARCHAR(255) NOT NULL, key VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D48A2F7C8A90ABA9 ON config (key)');
        $this->addSql('COMMENT ON COLUMN config.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN config.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN config.updated_at IS \'(DC2Type:datetime_immutable)\'');

        foreach (self::DEFAULT_CONFIG as $key => $config) {
            $this->addSql('INSERT INTO config (id, name, key, value, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)', [
                Uuid::uuid4(), $config['name'], $key, $config['value'], 'NOW()', 'NOW()'
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE config');
    }
}
