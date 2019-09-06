<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190905161905 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE plan (id UUID NOT NULL, name VARCHAR(255) CHECK(name IN (\'base\', \'expanded\', \'unlimited\')) DEFAULT \'unlimited\' NOT NULL, active BOOLEAN DEFAULT \'false\' NOT NULL, users_limit INT DEFAULT -1 NOT NULL, items_limit INT DEFAULT -1 NOT NULL, memory_limit INT DEFAULT -1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN plan.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN plan.name IS \'(DC2Type:BillingEnumType)\'');

        $this->addSql("INSERT INTO plan (id, name, users_limit, items_limit, memory_limit, active) VALUES (?,?,?,?,?,?)", [
            Uuid::uuid4(), 'unlimited', -1, -1, -1, true
        ]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE plan');
    }
}
