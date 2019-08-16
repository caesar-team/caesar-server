<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815165944 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE public_key_credential_source (id UUID NOT NULL, public_key_credential_id TEXT NOT NULL, type VARCHAR(255) NOT NULL, transports TEXT NOT NULL, attestation_type VARCHAR(255) NOT NULL, trust_path JSON NOT NULL, aaguid TEXT NOT NULL, credential_public_key TEXT NOT NULL, user_handle VARCHAR(255) NOT NULL, counter INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN public_key_credential_source.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN public_key_credential_source.public_key_credential_id IS \'(DC2Type:base64)\'');
        $this->addSql('COMMENT ON COLUMN public_key_credential_source.transports IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN public_key_credential_source.trust_path IS \'(DC2Type:trust_path)\'');
        $this->addSql('COMMENT ON COLUMN public_key_credential_source.aaguid IS \'(DC2Type:base64)\'');
        $this->addSql('COMMENT ON COLUMN public_key_credential_source.credential_public_key IS \'(DC2Type:base64)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE public_key_credential_source');
    }
}
