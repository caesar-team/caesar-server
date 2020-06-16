<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190417171114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE item ADD previous_list_id UUID DEFAULT NULL');
        $this->addSql("COMMENT ON COLUMN item.previous_list_id IS '(DC2Type:uuid)'");
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EC357C58 FOREIGN KEY (previous_list_id) REFERENCES directory (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F1B251EC357C58 ON item (previous_list_id)');
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema): void
    {
        $table = $schema->getTable('item');
        $table->dropColumn('previous_list_id');
    }
}
