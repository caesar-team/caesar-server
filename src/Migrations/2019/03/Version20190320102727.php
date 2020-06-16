<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190320102727 extends AbstractMigration
{
    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('invitation');
        $table->addColumn('shelf_life', 'string', [
            'default' => '+1 day',
            'notnull' => true,
            'length' => 10,
        ]);
        $this->addSql('ALTER TABLE invitation ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE invitation ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
    }

    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema): void
    {
        $table = $schema->getTable('invitation');
        $table->dropColumn('shelf_life');
        $table->dropColumn('created_at');
        $table->dropColumn('updated_at');
    }
}
