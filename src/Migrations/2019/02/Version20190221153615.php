<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190221153615 extends AbstractMigration
{
    /**
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema): void
    {
        $table = $schema->getTable('fos_user');
        $table->dropColumn('login');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fos_user ADD login VARCHAR(50) DEFAULT NULL');
        $this->addSql('UPDATE fos_user SET login = email');
    }
}
