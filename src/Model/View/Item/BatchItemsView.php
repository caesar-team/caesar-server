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
    private array $personal;

    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $shared;

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
    private array $system;

    /**
     * @var ItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $keypair;

    public function __construct()
    {
        $this->personal = [];
        $this->shared = [];
        $this->teams = [];
        $this->system = [];
        $this->keypair = [];
    }

    /**
     * @return ItemView[]
     */
    public function getPersonal(): array
    {
        return $this->personal;
    }

    /**
     * @param ItemView[] $personal
     */
    public function setPersonal(array $personal): void
    {
        $this->personal = $personal;
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
    public function getShared(): array
    {
        return $this->shared;
    }

    /**
     * @param ItemView[] $shared
     */
    public function setShared(array $shared): void
    {
        $this->shared = $shared;
    }

    /**
     * @return ItemView[]
     */
    public function getSystem(): array
    {
        return $this->system;
    }

    /**
     * @param ItemView[] $system
     */
    public function setSystem(array $system): void
    {
        $this->system = $system;
    }

    /**
     * @return ItemView[]
     */
    public function getKeypair(): array
    {
        return $this->keypair;
    }

    /**
     * @param ItemView[] $keypair
     */
    public function setKeypair(array $keypair): void
    {
        $this->keypair = $keypair;
    }
}
