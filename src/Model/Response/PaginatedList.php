<?php

declare(strict_types=1);

namespace App\Model\Response;

class PaginatedList
{
    /** @var array */
    private $data;

    /** @var int */
    private $totalPages;

    /** @var int */
    private $total;

    public function __construct(array $data, int $totalPages, int $total)
    {
        $this->data = $data;
        $this->totalPages = $totalPages;
        $this->total = $total;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }
}
