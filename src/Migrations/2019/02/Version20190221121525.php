<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190221121525 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('fos_user');
        $table->dropColumn('require_master_refresh');
        $table->addColumn('incomplete_share_flow', 'boolean', [
            'notnull' => true,
            'default' => false,
        ]);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('fos_user');
        $table->dropColumn('incomplete_share_flow');
        $table->addColumn('require_master_refresh', 'boolean', [
            'notnull' => true,
            'default' => false,
        ]);
    }
}
