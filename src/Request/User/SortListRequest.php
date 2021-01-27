<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Entity\Directory\AbstractDirectory;

final class SortListRequest
{
    /**
     * @var int|null
     */
    private $sort;

    private AbstractDirectory $directory;

    public function __construct(AbstractDirectory $directory)
    {
        $this->directory = $directory;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(?int $sort): void
    {
        $this->sort = $sort;
    }

    public function getDirectory(): AbstractDirectory
    {
        return $this->directory;
    }
}
