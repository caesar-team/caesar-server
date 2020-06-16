<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190226144312 extends AbstractMigration
{
    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('fos_user');
        $table->addColumn('backup_codes', 'json_array', [
            'notnull' => false,
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema): void
    {
        $table = $schema->getTable('fos_user');
        $table->dropColumn('backup_codes');
    }
}
