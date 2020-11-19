<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201119102626 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item RENAME COLUMN meta_attach_count TO meta_attachments_count');
        $this->addSql('ALTER TABLE item RENAME COLUMN meta_web_site TO meta_website');

        $this->addSql('ALTER TABLE item ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE item SET meta_title = title');
        $this->addSql('ALTER TABLE item DROP title');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item RENAME COLUMN meta_attachments_count TO meta_attach_count');
        $this->addSql('ALTER TABLE item RENAME COLUMN meta_website TO meta_web_site');

        $this->addSql('ALTER TABLE item ADD title TEXT DEFAULT NULL');
        $this->addSql('UPDATE item SET title = meta_title');
        $this->addSql('ALTER TABLE item DROP meta_title');
    }
}
