<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\Team;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class MemberListQuery
{
    private Team $team;

    /**
     * @var string[]
     */
    private array $ids;

    private bool $withoutKeypair;

    public function __construct(Team $team, Request $request)
    {
        $this->team = $team;
        $this->ids = $request->get('ids', []);
        $this->withoutKeypair = $request->query->getBoolean('without_keypair', false);

        $this->ids = array_filter($this->ids, static function (string $id) {
            return Uuid::isValid($id);
        });
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    /**
     * @return string[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }

    public function isWithoutKeypair(): bool
    {
        return $this->withoutKeypair;
    }
}
