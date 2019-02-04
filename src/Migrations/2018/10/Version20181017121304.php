<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Version20181017121304.
 */
final class Version20181017121304 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory ADD type VARCHAR(255) CHECK(type IN (\'list\', \'inbox\', \'trash\', \'credentials\')) NOT NULL');
        $this->addSql('COMMENT ON COLUMN directory.type IS \'(DC2Type:NodeEnumType)\'');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP type');
    }
}
