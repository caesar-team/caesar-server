<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190318102602 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $schema->dropTable('share_item');
        $schema->dropTable('shares');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shares (id UUID NOT NULL, owner_id UUID NOT NULL, user_id UUID NOT NULL, link VARCHAR(510) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_905F717C7E3C61F9 ON shares (owner_id)');
        $this->addSql('CREATE INDEX IDX_905F717CA76ED395 ON shares (user_id)');
        $this->addSql("COMMENT ON COLUMN shares.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN shares.owner_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN shares.user_id IS '(DC2Type:uuid)'");
        $this->addSql('CREATE TABLE share_item (id UUID NOT NULL, item_id UUID, share_id UUID NOT NULL, secret TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A2DC2887126F525E ON share_item (item_id)');
        $this->addSql('CREATE INDEX IDX_A2DC28872AE63FDB ON share_item (share_id)');
        $this->addSql("COMMENT ON COLUMN share_item.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN share_item.item_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN share_item.share_id IS '(DC2Type:uuid)'");
        $this->addSql('ALTER TABLE shares ADD CONSTRAINT FK_905F717C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shares ADD CONSTRAINT FK_905F717CA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE share_item ADD CONSTRAINT FK_A2DC2887126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE share_item ADD CONSTRAINT FK_A2DC28872AE63FDB FOREIGN KEY (share_id) REFERENCES shares (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
