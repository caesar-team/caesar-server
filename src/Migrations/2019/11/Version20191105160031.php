<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191105160031 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE notification_log (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN notification_log.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE share_log (id UUID NOT NULL, shared_item_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F5C0ED3533B85EE9 ON share_log (shared_item_id)');
        $this->addSql('COMMENT ON COLUMN share_log.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN share_log.shared_item_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE update_log (id UUID NOT NULL, update_id UUID DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_585815A6D596EAB1 ON update_log (update_id)');
        $this->addSql('COMMENT ON COLUMN update_log.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN update_log.update_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE share_log ADD CONSTRAINT FK_F5C0ED3533B85EE9 FOREIGN KEY (shared_item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE share_log ADD CONSTRAINT FK_F5C0ED35BF396750 FOREIGN KEY (id) REFERENCES notification_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE update_log ADD CONSTRAINT FK_585815A6D596EAB1 FOREIGN KEY (update_id) REFERENCES item_update (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE update_log ADD CONSTRAINT FK_585815A6BF396750 FOREIGN KEY (id) REFERENCES notification_log (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE share_log DROP CONSTRAINT FK_F5C0ED35BF396750');
        $this->addSql('ALTER TABLE update_log DROP CONSTRAINT FK_585815A6BF396750');
        $this->addSql('DROP TABLE notification_log');
        $this->addSql('DROP TABLE share_log');
        $this->addSql('DROP TABLE update_log');
    }
}
