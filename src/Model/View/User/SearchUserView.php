<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class SearchUserView
{
    /**
     * @SWG\Property(type="string", example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    private string $id;

    /**
     * @SWG\Property(type="string", example="email@gmail.com")
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
     * @SWG\Property(type="string[]", example={"b3d4d910-bf9d-4718-b93c-553f1e6711bb"})
     */
    private array $teamIds = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getName(): ?string
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
}
