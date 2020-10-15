<?php

declare(strict_types=1);

namespace App\Model\View\User;

use App\Entity\User;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "team_create",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_vault_create"
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\TeamVoter::CREATE'), object.getUser()))"
 *     )
 * )
 *
 * @Hateoas\Relation(
 *     "create_list",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_create_list"
 *     ),
 *     exclusion=@Hateoas\Exclusion(
 *         excludeIf="expr(not is_granted(constant('App\\Security\\Voter\\ListVoter::CREATE'), object.getUser()))"
 *     )
 * )
 */
class SelfUserInfoView
{
    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="ipopov@4xxi.com")
     */
    private string $email;

    /**
     * @SWG\Property(type="string", example="ipopov")
     */
    private string $name;

    /**
     * @SWG\Property(type="string", example="static/images/user/b3d4d910-bf9d-4718-b93c-553f1e6711bb.jpeg")
     */
    private ?string $avatar;

    /**
     * @var string[]
     *
     * @SWG\Property(type="string[]", example={"ROLE_USER"})
     */
    private array $roles;

    /**
     * @var string[]
     *
     * @SWG\Property(type="string[]", example={"a68833af-ab0f-4db3-acde-fccc47641b9e"})
     */
    private array $teamIds;

    /**
     * @Serializer\Exclude()
     */
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->roles = [];
        $this->teamIds = [];
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return string[]
     */
    public function getTeamIds(): array
    {
        return $this->teamIds;
    }

    /**
     * @param string[] $teamIds
     */
    public function setTeamIds(array $teamIds): void
    {
        $this->teamIds = $teamIds;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
