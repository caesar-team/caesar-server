<?php

declare(strict_types=1);

namespace App\Model\View\Share;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

final class ShareListView
{
    /**
     * @var ShareView[]
     *
     * @SWG\Property(type="array", @Model(type=ShareView::class))
     */
    private array $shares;

    public function __construct()
    {
        $this->shares = [];
    }

    /**
     * @return ShareView[]
     */
    public function getShares(): array
    {
        return $this->shares;
    }

    /**
     * @param ShareView[] $shares
     */
    public function setShares(array $shares): void
    {
        $this->shares = $shares;
    }
}
