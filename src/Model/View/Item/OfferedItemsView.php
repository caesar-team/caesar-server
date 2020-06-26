<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

final class OfferedItemsView
{
    /**
     * @var OfferedItemView[]
     *
     * @SWG\Property(type="array", @Model(type=OfferedItemView::class))
     */
    private array $personal;

    /**
     * @var OfferedTeamItemsView[]
     *
     * @SWG\Property(type="array", @Model(type=OfferedTeamItemsView::class))
     */
    private array $teams;

    public function __construct(array $personal = [], array $teams = [])
    {
        $this->personal = $personal;
        $this->teams = $teams;
    }

    /**
     * @return OfferedItemView[]
     */
    public function getPersonal(): array
    {
        return $this->personal;
    }

    /**
     * @return OfferedTeamItemsView[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }
}
