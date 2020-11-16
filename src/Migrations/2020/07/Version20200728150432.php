<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200728150432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE system_limit (id UUID NOT NULL, inspector VARCHAR(255) NOT NULL, "limit_size" INT DEFAULT -1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2B5973FA72DD518B ON system_limit (inspector)');
        $this->addSql('COMMENT ON COLUMN system_limit.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE system_limit');
        $this->addSql('COMMENT ON COLUMN item.team_favorite IS \'(DC2Type:array)\'');
    }
}
