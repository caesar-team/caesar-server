<?php

namespace App\Controller\Admin;

use App\Admin\Field\AssociationFieldWithSort;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController as BaseAbstractCrudControllerAlias;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

abstract class AbstractCrudController extends BaseAbstractCrudControllerAlias
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $queryBuilder->resetDQLPart('orderBy');
        $this->addOrderClause($queryBuilder, $searchDto, $entityDto, $fields);

        return $queryBuilder;
    }

    private function addOrderClause(QueryBuilder $queryBuilder, SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields): void
    {
        foreach ($searchDto->getSort() as $sortProperty => $sortOrder) {
            $field = $fields->get($sortProperty);

            $sortFieldIsDoctrineAssociation = $entityDto->isAssociation($sortProperty);
            if ($sortFieldIsDoctrineAssociation) {
                if (AssociationFieldWithSort::class === $field->getFieldFqcn()) {
                    $sortProperty = sprintf('%s.%s', $sortProperty, $field->getCustomOption(AssociationFieldWithSort::OPTION_SORT_FIELD));
                }
                $queryBuilder->addOrderBy($sortProperty, $sortOrder);
            } else {
                $queryBuilder->addOrderBy('entity.'.$sortProperty, $sortOrder);
            }
        }
    }
}
