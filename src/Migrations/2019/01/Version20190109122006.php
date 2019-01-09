<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190109122006 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE srp (id UUID NOT NULL, seed VARCHAR(255) NOT NULL, verifier VARCHAR(255) NOT NULL, public_client_ephemeral_value VARCHAR(255) DEFAULT NULL, public_server_ephemeral_value VARCHAR(255) DEFAULT NULL, private_server_ephemeral_value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN srp.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE fos_user ADD srp_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN fos_user.srp_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64794D59D1DD FOREIGN KEY (srp_id) REFERENCES srp (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957A64794D59D1DD ON fos_user (srp_id)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A64794D59D1DD');
        $this->addSql('DROP TABLE srp');
        $this->addSql('DROP INDEX UNIQ_957A64794D59D1DD');
        $this->addSql('ALTER TABLE fos_user DROP srp_id');
    }
}
