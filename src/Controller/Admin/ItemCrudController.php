<?php

namespace App\Controller\Admin;

use App\Entity\Item;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Item::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('type'),
            AssociationField::new('parentList'),
            AssociationField::new('team'),
            TextField::new('signedOwner')
                ->setTemplatePath('admin/fields/_user.html.twig'),
            DateTimeField::new('lastUpdated')
                ->setFormat('yyyy-MM-dd H:mm:s')
                ->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
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
            ->add(EntityFilter::new('team')->setFormTypeOptions($multipleOptions))
            ->add(EntityFilter::new('owner')->setFormTypeOptions($multipleOptions))
            ->add(EntityFilter::new('parentList')->setFormTypeOptions($multipleOptions))
        ;
    }
}
