<?php

namespace App\Controller\Admin;

use App\Entity\Directory;
use App\Entity\User;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonalListCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Directory::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $queryBuilder
            ->leftJoin('entity.parentList', 'parent_list')
            ->leftJoin(User::class, 'userLists', Join::WITH, 'userLists.lists = parent_list.id')
            ->leftJoin(User::class, 'user', Join::WITH, 'user.inbox = entity.id OR user.trash = entity.id')
            ->andWhere('userLists.id IS NOT NULL OR user.id IS NOT NULL')
        ;

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('label'),
            TextField::new('type')
                ->hideOnForm(),
            TextField::new('user')
                ->setTemplatePath('admin/fields/_user.html.twig')
                ->hideOnForm(),
            IntegerField::new('sort'),
        ];
    }
}
