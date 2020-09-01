<?php

namespace App\Controller\Admin;

use App\Entity\Directory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ListCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Directory::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('label'),
            TextField::new('type')
                ->hideOnForm(),
            AssociationField::new('parentList')
                ->hideOnForm(),
            IntegerField::new('sort'),
        ];
    }
}
