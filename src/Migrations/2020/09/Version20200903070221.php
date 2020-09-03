<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200903070221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory ADD user_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN directory.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DAA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_467844DAA76ED395 ON directory (user_id)');

        $this->addSql('UPDATE directory SET user_id = fos_user.id FROM fos_user WHERE directory.team_id IS NULL AND (fos_user.lists_id = directory.id OR fos_user.inbox_id = directory.id OR fos_user.trash_id = directory.id)');
        $this->addSql('UPDATE directory SET user_id = d2.user_id FROM directory as d2 WHERE d2.id = directory.parent_list_id');

        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A647918DA89DD');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A64799D26499B');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT FK_957A64792C87042F');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A647918DA89DD FOREIGN KEY (inbox_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64799D26499B FOREIGN KEY (lists_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT FK_957A64792C87042F FOREIGN KEY (trash_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE item DROP CONSTRAINT fk_5a8a6c8d4fe662cd');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E4FE662CD FOREIGN KEY (parent_list_id) REFERENCES directory (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

    }

    public function down(Schema $schema): void
    {
        $this->abortIf('postgresql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE directory DROP CONSTRAINT FK_467844DAA76ED395');
        $this->addSql('DROP INDEX IDX_467844DAA76ED395');
        $this->addSql('ALTER TABLE directory DROP user_id');

        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT fk_957a647918da89dd');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT fk_957a64799d26499b');
        $this->addSql('ALTER TABLE fos_user DROP CONSTRAINT fk_957a64792c87042f');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT fk_957a647918da89dd FOREIGN KEY (inbox_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT fk_957a64799d26499b FOREIGN KEY (lists_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fos_user ADD CONSTRAINT fk_957a64792c87042f FOREIGN KEY (trash_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E4FE662CD');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT fk_5a8a6c8d4fe662cd FOREIGN KEY (parent_list_id) REFERENCES directory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
