<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190225142258 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('fos_user');
        $table->addColumn('flow_status', 'string', [
            'notnull' => true,
            'default' => 'finished',
        ]);
        $table->dropColumn('incomplete_flow');
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('fos_user');
        $table->dropColumn('flow_status');
        $table->addColumn('incomplete_flow', 'boolean', [
            'notnull' => true,
            'default' => false,
        ]);
    }
}
