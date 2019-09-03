<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190903134610 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE audit (id UUID NOT NULL, users_count INT DEFAULT 0 NOT NULL, teams_count INT DEFAULT 0 NOT NULL, items_count INT DEFAULT 0 NOT NULL, memory_used INT DEFAULT 0 NOT NULL, billing_type VARCHAR(255) CHECK(billing_type IN (\'base\', \'expanded\')) DEFAULT \'base\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN audit.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN audit.billing_type IS \'(DC2Type:BillingEnumType)\'');

        $this->addSql("INSERT INTO audit (id) VALUES(?)", [Uuid::uuid4()]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE audit');
    }
}
