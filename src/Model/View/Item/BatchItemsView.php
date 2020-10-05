<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

final class BatchItemsView
{
    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $personals;

    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $shares;

    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $teams;

    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $systems;

    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $keypairs;

    public function __construct()
    {
        $this->personals = [];
        $this->shares = [];
        $this->teams = [];
        $this->systems = [];
        $this->keypairs = [];
    }

    /**
     * @return ItemView[]
     */
    public function getPersonals(): array
    {
        return $this->personals;
    }

    /**
     * @param ItemView[] $personals
     */
    public function setPersonals(array $personals): void
    {
        $this->personals = $personals;
    }

    /**
     * @return ItemView[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    /**
     * @param ItemView[] $teams
     */
    public function setTeams(array $teams): void
    {
        $this->teams = $teams;
    }

    /**
     * @return ItemView[]
     */
    public function getShares(): array
    {
        return $this->shares;
    }

    /**
     * @param ItemView[] $shares
     */
    public function setShares(array $shares): void
    {
        $this->shares = $shares;
    }

    /**
     * @return ItemView[]
     */
    public function getSystems(): array
    {
        return $this->systems;
    }

    /**
     * @param ItemView[] $systems
     */
    public function setSystems(array $systems): void
    {
        $this->systems = $systems;
    }

    /**
     * @return ItemView[]
     */
    public function getKeypairs(): array
    {
        return $this->keypairs;
    }

    /**
     * @param ItemView[] $keypairs
     */
    public function setKeypairs(array $keypairs): void
    {
        $this->keypairs = $keypairs;
    }
}
