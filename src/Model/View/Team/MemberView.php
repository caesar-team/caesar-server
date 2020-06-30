<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Entity\UserTeam;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "team_member_remove",
 *     attributes={"method": "DELETE"},
 *     href=@Hateoas\Route(
 *         "api_team_member_remove",
 *         parameters={ "team": "expr(object.getTeamId())", "user": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::REMOVE'), object.getUserTeam()))"
 *     )
 * )
 * @Hateoas\Relation(
 *     "team_member_edit",
 *     attributes={"method": "PATCH"},
 *     href=@Hateoas\Route(
 *         "api_team_member_edit",
 *         parameters={ "team": "expr(object.getTeamId())", "user": "expr(object.getId())" }
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\UserTeamVoter::EDIT'), object.getUserTeam()))"
 *     )
 * )
 */
final class MemberView
{
    /**
     * @SWG\Property(type="string", example="4fcc6aef-3fd6-4c16-9e4b-5c37486c7d46")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="some@email.com")
     */
    private string $email;

    /**
     * @SWG\Property(type="string", example="Avatar data")
     */
    private ?string $avatar;

    /**
     * @SWG\Property(type="string")
     */
    private ?string $publicKey;

    /**
     * @SWG\Property(type="string", example="Some name")
     */
    private string $name;

    /**
     * @SWG\Property(type="string[]")
     */
    private array $teamIds;

    /**
     * @SWG\Property(type="string", enum=UserTeam::ROLES)
     */
    private string $role;

    /**
     * @Serializer\Exclude
     */
    private string $teamId;

    /**
     * @Serializer\Exclude
     */
    private ?UserTeam $userTeam;

    public function __construct(?UserTeam $currentUserTeam)
    {
        $this->userTeam = $currentUserTeam;
        $this->teamId = '';
        if (null !== $currentUserTeam) {
            $this->teamId = $currentUserTeam->getTeam()->getId()->toString();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTeamIds(): array
    {
        return $this->teamIds;
    }

    public function setTeamIds(array $teamIds): void
    {
        $this->teamIds = $teamIds;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getUserTeam(): ?UserTeam
    {
        return $this->userTeam;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }
}
