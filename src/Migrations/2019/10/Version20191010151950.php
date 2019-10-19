<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191010151950 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item DROP CONSTRAINT fk_5a8a6c8dcd09addb');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E94EFFCA9 FOREIGN KEY (original_item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E94EFFCA9');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT fk_5a8a6c8dcd09addb FOREIGN KEY (original_item_id) REFERENCES item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
