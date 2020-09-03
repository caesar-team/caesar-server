<?php

namespace App\Controller\Admin;

use App\Entity\Directory;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

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
            ->leftJoin('entity.user', 'user')
            ->andWhere('user.id IS NOT NULL')
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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, 'Personal lists');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $multipleOptions = [
            'value_type_options' => [
                'multiple' => true,
                'attr' => [
                    'data-widget' => 'select2',
                ],
            ],
        ];

        return $filters
            ->add(EntityFilter::new('user')->setFormTypeOptions($multipleOptions))
        ;
    }
}
