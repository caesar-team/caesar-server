<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200611082301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item_update DROP CONSTRAINT FK_1EDC40B7896DBBDE');
        $this->addSql('ALTER TABLE item_update ADD CONSTRAINT FK_1EDC40B7896DBBDE FOREIGN KEY (updated_by_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item_update DROP CONSTRAINT fk_1edc40b7896dbbde');
        $this->addSql('ALTER TABLE item_update ADD CONSTRAINT fk_1edc40b7896dbbde FOREIGN KEY (updated_by_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
