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
     * @SWG\Property(type="string", example="-----BEGIN PGP PUBLIC KEY BLOCK----- Version: OpenPGP v4.3.0 mQENBFwbZEwBCAC5BmDb4KV2pvWY5+fLV1qKotfAqpMP5utFKrytkLqoxMqsBGx4 M5rmYqddbPRSJ6k0920KSEzvicdyv3xM5ICpg6pCuh8YzFXKZNPDRfKijwOr18nz wMDwDF/7E2aBxIau3QNj9z2glg/VNo8vVXcXrq2aIIymisWgllFBVo1K39dhLHFB 25AMFbUS0UDIEQvMTK4Ed7Wmaply118vGP9T3e72tDX2eMwLST1L47h3j5U4YdNA LrI0wOoLY15+lpsOAf5FfyNpnmS3IA36K6o8KC8ns9vFa5zgYQb64H0wwY6DS8LN CN3WnrA4uW4CyR0QTjZlqOyz8JxYnxnTrhchABEBAAG0FGRzcGlyaWRvbm92QDR4 eGkuY29tiQEcBBABAgAGBQJcG2RMAAoJECWcicDF9iKz1UgH/3mCoFldkmGpFyzO KR5oCEs4520dhYConki+N/WcJu/24VFmjbdz3nab0JzrN4K5MRGKf/z10o6rfwvk ZtOpJeDu2HCWjTA79ej/cg26RNz0884sCSHyUpeGrM3kPezSLBSwy1C26DgvvbpL 3i2p/bRwYk8PwMqYfxrxC2NbjS8TqkSuNqufgBcvueIyPmb5OoF3hEzVPHXWYiGg zpNBlJy/6vS4yzRIqGqJ3zvOUO/b2GKMJY2YmiB6JFOyvViPGbIvUtHEoox+mLuT tD953zk7pJ1kkr+PZMj9k1xsOiE/8zq1SkgBQKkJTVC2ODaF52z9DOHCRyzf3bzW J/U5ZkU= =BG8s -----END PGP PUBLIC KEY BLOCK-----")
     */
    private ?string $publicKey;

    /**
     * @var string[]
     *
     * @SWG\Property(type="string[]", example={"ROLE_USER"})
     */
    private array $domainRoles;

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
        $this->domainRoles = [];
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
    public function getDomainRoles(): array
    {
        return $this->domainRoles;
    }

    /**
     * @param string[] $domainRoles
     */
    public function setDomainRoles(array $domainRoles): void
    {
        $this->domainRoles = $domainRoles;
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

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }
}
