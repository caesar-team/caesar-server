<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201002133200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $updateSql = <<<SQL
UPDATE directory SET user_id = (SELECT user_id FROM directory as d WHERE d.id = directory.parent_list_id)
WHERE directory.label = 'default' AND directory.team_id IS NULL AND directory.user_id IS NULL
SQL;

        $this->addSql($updateSql);
    }

    public function down(Schema $schema): void
    {
    }
}
