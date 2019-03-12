<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190312102902 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("CREATE TABLE item_mask (id UUID NOT NULL, item_id UUID NOT NULL, recipient_id UUID NOT NULL, secret TEXT NOT NULL, access VARCHAR(255) CHECK(access IN ('read', 'write')) NOT NULL, last_updated TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_B75F87CB126F525E ON item_mask (item_id)");
        $this->addSql("CREATE INDEX IDX_B75F87CBE92F8F78 ON item_mask (recipient_id)");
        $this->addSql("COMMENT ON COLUMN item_mask.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN item_mask.item_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN item_mask.recipient_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN item_mask.access IS '(DC2Type:AccessEnumType)'");
        $this->addSql("ALTER TABLE item_mask ADD CONSTRAINT FK_B75F87CB126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE item_mask ADD CONSTRAINT FK_B75F87CBE92F8F78 FOREIGN KEY (recipient_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('item_mask');
    }
}
