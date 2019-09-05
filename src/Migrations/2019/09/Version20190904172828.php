<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190904172828 extends AbstractMigration
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

        $this->addSql('CREATE TABLE plan (id UUID NOT NULL, name VARCHAR(255) CHECK(name IN (\'base\', \'expanded\')) DEFAULT \'unlimited\' NOT NULL, users_limit INT DEFAULT -1 NOT NULL, items_limit INT DEFAULT -1 NOT NULL, memory_limit INT DEFAULT -1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN plan.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN plan.name IS \'(DC2Type:BillingEnumType)\'');
        $this->addSql('CREATE TABLE project_usage (id UUID NOT NULL, billing_plan_id UUID DEFAULT NULL, users_count INT DEFAULT 0 NOT NULL, teams_count INT DEFAULT 0 NOT NULL, items_count INT DEFAULT 0 NOT NULL, memory_used INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9218FF7983B7894C ON project_usage (billing_plan_id)');
        $this->addSql('COMMENT ON COLUMN project_usage.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN project_usage.billing_plan_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN project_usage.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN project_usage.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE project_usage ADD CONSTRAINT FK_9218FF7983B7894C FOREIGN KEY (billing_plan_id) REFERENCES plan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql("ALTER INDEX idx_9218ff7983b7894c RENAME TO IDX_C3BE57EE83B7894C");

        $planId = Uuid::uuid4();
        $this->addSql("INSERT INTO plan (id, name, users_limit, items_limit, memory_limit) VALUES(?,?,?,?,?)", [
            $planId,
            "base",
            100,
            1000,
            1073741824,
        ]);

        $now = new \DateTimeImmutable();
        $nowString = $now->format('Y-m-d H:i:s');
        $this->addSql("INSERT INTO project_usage (id, created_at, updated_at, billing_plan_id) VALUES (?,?,?,?)", [
            Uuid::uuid4(),
            $nowString,
            $nowString,
            $planId
        ]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE project_usage DROP CONSTRAINT FK_9218FF7983B7894C');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE project_usage');
    }
}
