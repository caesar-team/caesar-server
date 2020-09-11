<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\Model\View\Team\TeamItemView;
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
     * @var TeamItemView[]
     *
     * @SWG\Property(type="array", @Model(type=ItemView::class))
     */
    private array $teams;

    public function __construct()
    {
        $this->personal = [];
        $this->shared = [];
        $this->teams = [];
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
}
