<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181217084829 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fos_user ADD encrypted_private_key VARCHAR(65525) DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user ADD public_key VARCHAR(65525) DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user DROP keys');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fos_user ADD keys JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user DROP encrypted_private_key');
        $this->addSql('ALTER TABLE fos_user DROP public_key');
    }
}
