<?php

declare(strict_types=1);

namespace App\Model\Query;

use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractQuery
{
    protected const PER_PAGE = 100;
    protected const FIRST_PAGE = 1;

    /**
     * @var int|null
     *
     * @Assert\GreaterThanOrEqual(1)
     */
    protected $page;

    /**
     * @var int|null
     *
     * @Assert\Range(min="1", max="500")
     */
    protected $perPage;

    public function getPage(): int
    {
        return $this->page > 0 ? $this->page : static::FIRST_PAGE;
    }

    /**
     * @param int $page
     */
    public function setPage(?int $page): void
    {
        $this->page = $page;
    }

    public function getPerPage(): int
    {
        return $this->perPage > 0 ? $this->perPage : static::PER_PAGE;
    }

    /**
     * @return $this
     */
    public function setPerPage(?int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getFirstResult(): int
    {
        return $this->getPerPage() * ($this->getPage() - 1);
    }
}
