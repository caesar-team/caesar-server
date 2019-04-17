<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190311124619 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE groups (id UUID NOT NULL, alias VARCHAR(50) NOT NULL, title VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "groups".id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE user_group (id UUID NOT NULL, group_id UUID NOT NULL, user_id UUID NOT NULL, user_role VARCHAR(50) DEFAULT \'member\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id));');
        $this->addSql('CREATE INDEX IDX_8F02BF9DFE54D947 ON user_group (group_id)');
        $this->addSql('CREATE INDEX IDX_8F02BF9DA76ED395 ON user_group (user_id)');
        $this->addSql('COMMENT ON COLUMN user_group.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_group.group_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_group.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9DFE54D947 FOREIGN KEY (group_id) REFERENCES "groups" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_group ADD CONSTRAINT FK_8F02BF9DA76ED395 FOREIGN KEY (user_id) REFERENCES fos_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $uuid = Uuid::uuid4();
        $this->addSql("INSERT INTO \"groups\" (id, alias, title) VALUES ('{$uuid}','default', 'Default')");
        $users = $this->connection->fetchAll("SELECT id FROM fos_user");

        foreach ($users as $user) {
            $userGroupUuid = Uuid::uuid4();
            $userUuid = $user['id'];
            $createdAt = (new \DateTime())->format('Y-m-d H:i:s');
            $this->addSql("INSERT INTO user_group (id, group_id, user_id, created_at, updated_at, user_role) VALUES ('{$userGroupUuid}', '{$uuid}', '{$userUuid}', '{$createdAt}', '{$createdAt}', 'member')");
        }
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('groups');
        $schema->dropTable('user_group');
    }
}
