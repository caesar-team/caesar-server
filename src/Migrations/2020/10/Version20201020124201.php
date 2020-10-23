<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201020124201 extends AbstractMigration
{
    private const ROLE_MAPS = [
        'admin' => 'ROLE_ADMIN',
        'member' => 'ROLE_MEMBER',
        'guest' => 'ROLE_GUEST',
        'pretender' => 'ROLE_PRETENDER',
    ];

    public function up(Schema $schema): void
    {
        foreach (self::ROLE_MAPS as $oldRole => $newRole) {
            $this->addSql('UPDATE user_group SET user_role = :new_role WHERE user_role = :old_role', [
                'new_role' => $newRole,
                'old_role' => $oldRole,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        foreach (self::ROLE_MAPS as $oldRole => $newRole) {
            $this->addSql('UPDATE user_group SET user_role = :old_role WHERE user_role = :new_role', [
                'new_role' => $newRole,
                'old_role' => $oldRole,
            ]);
        }
    }
}
