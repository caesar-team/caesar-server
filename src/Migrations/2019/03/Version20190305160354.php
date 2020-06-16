<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190305160354 extends AbstractMigration
{
    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('item');
        $table->addColumn('sort', 'integer', [
            'notnull' => true,
            'default' => 0,
        ]);
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema): void
    {
        $table = $schema->getTable('item');
        $table->dropColumn('sort');
    }
}
