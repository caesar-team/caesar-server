<?php

declare(strict_types=1);

namespace App\Model\Response;

class PaginatedList
{
    private array $data;

    private int $totalPages;

    private int $total;

    public function __construct(array $data, int $totalPages, int $total)
    {
        $this->data = $data;
        $this->totalPages = $totalPages;
        $this->total = $total;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
