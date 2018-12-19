<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181218114201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE share_post RENAME COLUMN post_id to item_id');
        $this->addSql('ALTER INDEX share_post_pkey RENAME TO share_item_pkey');
        $this->addSql('ALTER TABLE share_post RENAME TO share_item');

        $this->addSql('ALTER TABLE post_tags RENAME COLUMN post_id to item_id');
        $this->addSql('ALTER INDEX post_tags_pkey RENAME TO item_tags_pkey');
        $this->addSql('ALTER TABLE post_tags RENAME TO item_tags');

        $this->addSql('ALTER TABLE post RENAME COLUMN original_post_id to original_item_id');
        $this->addSql('ALTER INDEX post_pkey RENAME TO item_pkey');
        $this->addSql('ALTER TABLE post RENAME TO item');

        $this->addSql('ALTER INDEX idx_e74d61144b89032c RENAME TO IDX_A2DC2887126F525E');
        $this->addSql('ALTER INDEX idx_e74d61142ae63fdb RENAME TO IDX_A2DC28872AE63FDB');
        $this->addSql('ALTER INDEX idx_5a8a6c8d4fe662cd RENAME TO IDX_1F1B251E4FE662CD');
        $this->addSql('ALTER INDEX idx_5a8a6c8dcd09addb RENAME TO IDX_1F1B251E94EFFCA9');
        $this->addSql('ALTER INDEX idx_a6e9f32d4b89032c RENAME TO IDX_A78CD0DD126F525E');
        $this->addSql('ALTER INDEX idx_a6e9f32dbad26311 RENAME TO IDX_A78CD0DDBAD26311');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE share_item RENAME COLUMN item_id to post_id');
        $this->addSql('ALTER INDEX share_item_pkey RENAME TO share_post_pkey');
        $this->addSql('ALTER TABLE share_item RENAME TO share_post');

        $this->addSql('ALTER TABLE item_tags RENAME COLUMN item_id to post_id');
        $this->addSql('ALTER INDEX item_tags_pkey RENAME TO post_tags_pkey');
        $this->addSql('ALTER TABLE item_tags RENAME TO post_tags');

        $this->addSql('ALTER TABLE item RENAME COLUMN original_item_id to original_post_id');
        $this->addSql('ALTER INDEX item_pkey RENAME TO post_pkey');
        $this->addSql('ALTER TABLE item RENAME TO post');

        $this->addSql('ALTER INDEX idx_1f1b251e4fe662cd RENAME TO idx_5a8a6c8d4fe662cd');
        $this->addSql('ALTER INDEX idx_1f1b251e94effca9 RENAME TO idx_5a8a6c8dcd09addb');
        $this->addSql('ALTER INDEX idx_a2dc28872ae63fdb RENAME TO idx_e74d61142ae63fdb');
        $this->addSql('ALTER INDEX idx_a2dc2887126f525e RENAME TO idx_e74d61144b89032c');
        $this->addSql('ALTER INDEX idx_a78cd0dd126f525e RENAME TO idx_a6e9f32d4b89032c');
        $this->addSql('ALTER INDEX idx_a78cd0ddbad26311 RENAME TO idx_a6e9f32dbad26311');
    }
}
