<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190903101641 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql("CREATE EXTENSION file_fdw");
        $this->addSql("CREATE SERVER fileserver FOREIGN DATA WRAPPER file_fdw");
        $this->addSql("CREATE FOREIGN TABLE loadavg 
(one text, five text, fifteen text, scheduled text, pid text) 
SERVER fileserver 
OPTIONS (filename '/proc/loadavg', format 'text', delimiter ' ')");
        $this->addSql("CREATE FOREIGN TABLE meminfo 
(stat text, value text) 
SERVER fileserver 
OPTIONS (filename '/proc/meminfo', format 'csv', delimiter ':')");
    }

    public function down(Schema $schema) : void
    {
        $this->addSql("DROP FOREIGN TABLE loadavg");
        $this->addSql("DROP FOREIGN TABLE meminfo");
        $this->addSql("DROP SERVER fileserver");
        $this->addSql("DROP EXTENSION file_fdw");
    }
}
