<?php

declare(strict_types=1);

namespace App\Request\User;

use App\Entity\Directory;

final class SortListRequest
{
    /**
     * @var int|null
     */
    private $sort;

    private Directory $directory;

    public function __construct(Directory $directory)
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

    public function getDirectory(): Directory
    {
        return $this->directory;
    }
}
