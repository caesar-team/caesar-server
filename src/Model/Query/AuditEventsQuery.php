<?php

declare(strict_types=1);

namespace App\Model\Query;

use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class AuditEventsQuery extends AbstractQuery
{
    public const PAGE_PARAM = 'page';
    public const PAGE_SIZE_PARAM = 'limit';
    public const PAGE_SIZE_DEFAULT = 30;
    public const FILTER_TAB = 'tab';
    public const FILTER_DATE_FROM = 'date_from';
    public const FILTER_DATE_TO = 'date_to';

    public const TAB_SHARED = 'shared';
    public const TAB_PERSONAL = 'personal';

    private const FILTER_DATE_FORMAT = 'd-m-Y H:i';
    private const FILTER_DATE_SHORT_FORMAT = 'd-m-Y';
    private const FILTER_TAB_AVAILABLE = [
        self::TAB_SHARED, self::TAB_PERSONAL,
    ];

    /**
     * @var User
     */
    private $user;

    /**
     * @var Item
     */
    private $item;

    /**
     * @var string|null
     */
    private $tab;

    /**
     * @var \DateTime|null
     */
    private $dateFrom;

    /**
     * @var \DateTime|null
     */
    private $dateTo;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public static function fromRequest(User $user, Request $request, int $pageSizeDefault = self::PAGE_SIZE_DEFAULT): self
    {
        $page = $request->query->getInt(self::PAGE_PARAM, 1);
        $pageSize = $request->query->getInt(self::PAGE_SIZE_PARAM, $pageSizeDefault);

        $query = new self($user);
        $query->setPage($page);
        $query->setPerPage($pageSize);

        $tab = $request->query->get(self::FILTER_TAB);
        if (in_array($tab, self::FILTER_TAB_AVAILABLE)) {
            $query->setTab($tab);
        }
        $dateFrom = $request->query->get(self::FILTER_DATE_FROM, '');
        $dateFormat = 10 == mb_strlen($dateFrom) ? self::FILTER_DATE_SHORT_FORMAT : self::FILTER_DATE_FORMAT;
        if ($dateFrom && $dateTimeFrom = \DateTime::createFromFormat($dateFormat, $dateFrom)) {
            if (self::FILTER_DATE_SHORT_FORMAT == $dateFormat) {
                $dateTimeFrom->setTime(0, 0, 0);
            }

            $query->setDateFrom($dateTimeFrom);
        }
        $dateTo = $request->query->get(self::FILTER_DATE_TO, '');
        $dateFormat = 10 == mb_strlen($dateTo) ? self::FILTER_DATE_SHORT_FORMAT : self::FILTER_DATE_FORMAT;
        if ($dateTo && $dateTimeTo = \DateTime::createFromFormat($dateFormat, $dateTo)) {
            if (self::FILTER_DATE_SHORT_FORMAT == $dateFormat) {
                $dateTimeTo->setTime(23, 59, 59);
            }

            $query->setDateTo($dateTimeTo);
        }

        return $query;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): void
    {
        $this->item = $item;
    }

    public function getTab(): ?string
    {
        return $this->tab;
    }

    public function setTab(?string $tab): void
    {
        $this->tab = $tab;
    }

    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTime $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTime $dateTo): void
    {
        $this->dateTo = $dateTo;
    }
}
