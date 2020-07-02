<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200630070018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_467844DA4FE662CD');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DA4FE662CD FOREIGN KEY (parent_list_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP CONSTRAINT fk_467844da4fe662cd');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT fk_467844da4fe662cd FOREIGN KEY (parent_list_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
