<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181214125900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE shares (id UUID NOT NULL, owner_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_905F717C7E3C61F9 ON shares (owner_id)');
        $this->addSql('CREATE INDEX IDX_905F717CA76ED395 ON shares (user_id)');
        $this->addSql('COMMENT ON COLUMN shares.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN shares.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN shares.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE share_post (id UUID NOT NULL, post_id UUID NOT NULL, share_id UUID NOT NULL, secret TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E74D61144B89032C ON share_post (post_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E74D61142AE63FDB ON share_post (share_id)');
        $this->addSql('COMMENT ON COLUMN share_post.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN share_post.post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN share_post.share_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE shares ADD CONSTRAINT FK_905F717C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shares ADD CONSTRAINT FK_905F717CA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE share_post ADD CONSTRAINT FK_E74D61144B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE share_post ADD CONSTRAINT FK_E74D61142AE63FDB FOREIGN KEY (share_id) REFERENCES shares (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD guest BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE share_post DROP CONSTRAINT FK_E74D61142AE63FDB');
        $this->addSql('DROP TABLE shares');
        $this->addSql('DROP TABLE share_post');
        $this->addSql('ALTER TABLE fos_user DROP guest');
    }
}
