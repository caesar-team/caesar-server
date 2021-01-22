<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20201225102236 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Refactoring directory';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
        $this->addSql('CREATE TABLE favorite_team_item (id UUID NOT NULL, item_id UUID NOT NULL, team_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4EA44F7126F525E ON favorite_team_item (item_id)');
        $this->addSql('CREATE INDEX IDX_4EA44F7296CD8AE ON favorite_team_item (team_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_favorite_team_item ON favorite_team_item (item_id, team_id)');
        $this->addSql('COMMENT ON COLUMN favorite_team_item.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN favorite_team_item.item_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN favorite_team_item.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE favorite_user_item (id UUID NOT NULL, item_id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AF8AFC45126F525E ON favorite_user_item (item_id)');
        $this->addSql('CREATE INDEX IDX_AF8AFC45A76ED395 ON favorite_user_item (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_favorite_user_item ON favorite_user_item (item_id, user_id)');
        $this->addSql('COMMENT ON COLUMN favorite_user_item.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN favorite_user_item.item_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN favorite_user_item.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE favorite_team_item ADD CONSTRAINT FK_4EA44F7126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite_team_item ADD CONSTRAINT FK_4EA44F7296CD8AE FOREIGN KEY (team_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite_user_item ADD CONSTRAINT FK_AF8AFC45126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE favorite_user_item ADD CONSTRAINT FK_AF8AFC45A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('INSERT INTO favorite_user_item (id, item_id, user_id) SELECT uuid_generate_v4(), id, owner_id FROM item WHERE favorite = true');

        $this->addSql('ALTER TABLE item DROP favorite');
        $this->addSql('ALTER TABLE item DROP team_favorite');


        $this->addSql('ALTER TABLE directory DROP CONSTRAINT fk_467844da4fe662cd');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT fk_1f1b251ec357c58');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT fk_1f1b251e4fe662cd');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT fk_957a647918da89dd');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT fk_957a64799d26499b');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT fk_957a64792c87042f');
        $this->addSql('ALTER TABLE groups DROP CONSTRAINT fk_f06d39709d26499b');
        $this->addSql('ALTER TABLE groups DROP CONSTRAINT fk_f06d39702c87042f');
        $this->addSql('ALTER TABLE directory RENAME TO old_directory');

        $this->addSql('DROP INDEX IDX_467844DAA76ED395');
        $this->addSql('DROP INDEX IDX_467844DA296CD8AE');

        $this->addSql('CREATE TABLE directory (id UUID NOT NULL, parent_directory_id UUID DEFAULT NULL, user_id UUID DEFAULT NULL, team_id UUID DEFAULT NULL, label VARCHAR(255) NOT NULL, sort INT DEFAULT 0 NOT NULL, type VARCHAR(255) CHECK(type IN (\'default\', \'root\', \'list\', \'inbox\', \'trash\')) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, object VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_467844DA7CFA5BB1 ON directory (parent_directory_id)');
        $this->addSql('CREATE INDEX IDX_467844DAA76ED395 ON directory (user_id)');
        $this->addSql('CREATE INDEX IDX_467844DA296CD8AE ON directory (team_id)');
        $this->addSql('COMMENT ON COLUMN directory.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.parent_directory_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.type IS \'(DC2Type:DirectoryEnumType)\'');
        $this->addSql('COMMENT ON COLUMN directory.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE directory_item (id UUID NOT NULL, item_id UUID NOT NULL, directory_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_69CC2CFC126F525E ON directory_item (item_id)');
        $this->addSql('CREATE INDEX IDX_69CC2CFC2C94069F ON directory_item (directory_id)');
        $this->addSql('COMMENT ON COLUMN directory_item.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory_item.item_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory_item.directory_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_BE4E5C77CFA5BB1 FOREIGN KEY (parent_directory_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_BE4E5C7A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_BE4E5C7296CD8AE FOREIGN KEY (team_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory_item ADD CONSTRAINT FK_69CC2CFC126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory_item ADD CONSTRAINT FK_69CC2CFC2C94069F FOREIGN KEY (directory_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('INSERT INTO directory (id, parent_directory_id, user_id, label, sort, type, created_at, object) SELECT id, parent_list_id, user_id, label, sort, type, created_at, \'App\\Entity\\Directory\\UserDirectory\' FROM old_directory WHERE user_id IS NOT NULL');
        $this->addSql('INSERT INTO directory (id, parent_directory_id, team_id, label, sort, type, created_at, object) SELECT id, parent_list_id, team_id, label, sort, type, created_at, \'App\\Entity\\Directory\\TeamDirectory\' FROM old_directory WHERE team_id IS NOT NULL');
        $this->addSql('INSERT INTO directory_item (id, item_id, directory_id) SELECT uuid_generate_v4(), id, parent_list_id FROM item');

        $this->addSql('DROP TABLE old_directory');
        $this->addSql('DROP INDEX uniq_957a64792c87042f');
        $this->addSql('DROP INDEX uniq_957a647918da89dd');
        $this->addSql('DROP INDEX uniq_957a64799d26499b');
        $this->addSql('ALTER TABLE fos_user DROP inbox_id');
        $this->addSql('ALTER TABLE fos_user DROP lists_id');
        $this->addSql('ALTER TABLE fos_user DROP trash_id');
        $this->addSql('DROP INDEX uniq_f06d39709d26499b');
        $this->addSql('DROP INDEX uniq_f06d39702c87042f');
        $this->addSql('ALTER TABLE groups DROP lists_id');
        $this->addSql('ALTER TABLE groups DROP trash_id');
        $this->addSql('DROP INDEX idx_1f1b251ec357c58');
        $this->addSql('DROP INDEX idx_1f1b251e4fe662cd');
        $this->addSql('ALTER TABLE item DROP parent_list_id');
        $this->addSql('ALTER TABLE item DROP previous_list_id');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP TABLE favorite_team_item');
        $this->addSql('DROP TABLE favorite_user_item');
        $this->addSql('ALTER TABLE item ADD favorite BOOLEAN DEFAULT \'false\' NOT NULL');
        $this->addSql('ALTER TABLE item ADD team_favorite TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN item.team_favorite IS \'(DC2Type:array)\'');


        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_BE4E5C77CFA5BB1');
        $this->addSql('ALTER TABLE directory_item DROP CONSTRAINT FK_69CC2CFC2C94069F');
        $this->addSql('ALTER TABLE directory RENAME TO old_directory');

        $this->addSql('CREATE TABLE directory (id UUID NOT NULL, parent_list_id UUID DEFAULT NULL, team_id UUID DEFAULT NULL, user_id UUID DEFAULT NULL, label VARCHAR(255) NOT NULL, type VARCHAR(255) CHECK(type IN (\'default\', \'root\', \'list\', \'inbox\', \'trash\')) NOT NULL, sort INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_467844daa76ed395 ON directory (user_id)');
        $this->addSql('CREATE INDEX idx_467844da4fe662cd ON directory (parent_list_id)');
        $this->addSql('CREATE INDEX idx_467844da296cd8ae ON directory (team_id)');
        $this->addSql('COMMENT ON COLUMN directory.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.parent_list_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.team_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN directory.type IS \'(DC2Type:DirectoryEnumType)\'');
        $this->addSql('COMMENT ON COLUMN directory.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT fk_467844da4fe662cd FOREIGN KEY (parent_list_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT fk_467844da296cd8ae FOREIGN KEY (team_id) REFERENCES groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT fk_467844daa76ed395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE item ADD parent_list_id UUID NOT NULL');
        $this->addSql('ALTER TABLE item ADD previous_list_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN item.parent_list_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN item.previous_list_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT fk_1f1b251ec357c58 FOREIGN KEY (previous_list_id) REFERENCES directory (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT fk_1f1b251e4fe662cd FOREIGN KEY (parent_list_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1f1b251ec357c58 ON item (previous_list_id)');
        $this->addSql('CREATE INDEX idx_1f1b251e4fe662cd ON item (parent_list_id)');
        $this->addSql('ALTER TABLE fos_user ADD inbox_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user ADD lists_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE fos_user ADD trash_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN fos_user.inbox_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN fos_user.lists_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN fos_user.trash_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT fk_957a647918da89dd FOREIGN KEY (inbox_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT fk_957a64799d26499b FOREIGN KEY (lists_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT fk_957a64792c87042f FOREIGN KEY (trash_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_957a64792c87042f ON fos_user (trash_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_957a647918da89dd ON fos_user (inbox_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_957a64799d26499b ON fos_user (lists_id)');
        $this->addSql('ALTER TABLE groups ADD lists_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE groups ADD trash_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN groups.lists_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN groups.trash_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT fk_f06d39709d26499b FOREIGN KEY (lists_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE groups ADD CONSTRAINT fk_f06d39702c87042f FOREIGN KEY (trash_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_f06d39709d26499b ON groups (lists_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_f06d39702c87042f ON groups (trash_id)');

        $this->addSql('INSERT INTO directory (id, parent_list_id, user_id, label, sort, type, created_at) SELECT id, parent_directory_id, user_id, label, sort, type, created_at FROM old_directory WHERE object = \'App\\Entity\\Directory\\UserDirectory\'');
        $this->addSql('INSERT INTO directory (id, parent_list_id, team_id, label, sort, type, created_at) SELECT id, parent_directory_id, team_id, label, sort, type, created_at FROM old_directory WHERE object = \'App\\Entity\\Directory\\TeamDirectory\'');
        $this->addSql('UPDATE item SET parent_list_id = directory_item.directory_id FROM directory_item WHERE item_id = item.id');
        $this->addSql('UPDATE fos_user SET inbox_id = directory.id FROM directory WHERE user_id = fos_user.id AND type = ?', ['inbox']);
        $this->addSql('UPDATE fos_user SET lists_id = directory.id FROM directory WHERE user_id = fos_user.id AND type = ?', ['root']);
        $this->addSql('UPDATE fos_user SET trash_id = directory.id FROM directory WHERE user_id = fos_user.id AND type = ?', ['trash']);
        $this->addSql('UPDATE groups SET lists_id = directory.id FROM directory WHERE team_id = groups.id AND type = ?', ['root']);
        $this->addSql('UPDATE groups SET trash_id = directory.id FROM directory WHERE team_id = groups.id AND type = ?', ['trash']);

        $this->addSql('DROP TABLE old_directory');
        $this->addSql('DROP TABLE directory_item');
    }
}
