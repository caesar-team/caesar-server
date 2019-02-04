<?php

declare(strict_types=1);

namespace App\Traits;

use App\Model\Query\AbstractQuery;
use App\Model\Response\PaginatedList;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Gedmo\Translatable\Query\TreeWalker\TranslationWalker;

trait PaginatorTrait
{
    protected function createPaginatedList(QueryBuilder $qb, AbstractQuery $query, bool $translate = false): PaginatedList
    {
        $paginator = new Paginator($qb);

        $dbQuery = $paginator->getQuery();
        if ($translate) {
            $dbQuery->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, TranslationWalker::class);
        }
        $totalPages = (int) \ceil($paginator->count() / $query->getPerPage());

        return new PaginatedList(
            $dbQuery->getResult(),
            $totalPages,
            $paginator->count()
        );
    }
}
