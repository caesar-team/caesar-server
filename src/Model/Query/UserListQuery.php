<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\User;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class UserListQuery
{
    private array $ids;

    private ?string $role;

    private bool $domain;

    public function __construct(Request $request)
    {
        $this->ids = $request->get('ids', []);
        $this->role = $request->get('role');
        $this->domain = $request->query->getBoolean('is_domain_user', false);

        $this->ids = array_filter($this->ids, static function (string $id) {
            return Uuid::isValid($id);
        });

        if (!in_array($this->role, [User::ROLE_USER, User::ROLE_ADMIN])) {
            $this->role = null;
        }
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function isDomain(): bool
    {
        return $this->domain;
    }
}
