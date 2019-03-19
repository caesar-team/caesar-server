<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190318160204 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema) : void
    {
        $schema->dropTable('item_mask');
        $itemTable = $schema->getTable('item');
        $itemTable->addColumn('status', 'string', [
           'notnull' => true,
           'default' => 'finished',
            'length' => 10
        ]);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema) : void
    {
        $itemTable = $schema->getTable('item');
        $itemTable->dropColumn('status');
        $this->addSql("CREATE TABLE item_mask (id UUID NOT NULL, item_id UUID, recipient_id UUID NOT NULL, secret TEXT NOT NULL, access VARCHAR(255) CHECK(access IN ('read', 'write')) NOT NULL, link VARCHAR(510) DEFAULT NULL, cause VARCHAR(10) DEFAULT 'invite', created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))");
        $this->addSql("CREATE INDEX IDX_B75F87CB126F525E ON item_mask (item_id)");
        $this->addSql("CREATE INDEX IDX_B75F87CBE92F8F78 ON item_mask (recipient_id)");
        $this->addSql("COMMENT ON COLUMN item_mask.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN item_mask.item_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN item_mask.recipient_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN item_mask.access IS '(DC2Type:AccessEnumType)'");
        $this->addSql("ALTER TABLE item_mask ADD CONSTRAINT FK_B75F87CB126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        $this->addSql("ALTER TABLE item_mask ADD CONSTRAINT FK_B75F87CBE92F8F78 FOREIGN KEY (recipient_id) REFERENCES fos_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    }
}
