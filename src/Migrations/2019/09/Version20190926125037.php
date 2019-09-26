<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\DefaultIcon;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190926125037 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("UPDATE groups SET icon=? WHERE icon IS NULL", [DefaultIcon::getDefaultIcon()]);
    }

    public function down(Schema $schema) : void
    {

    }
}
