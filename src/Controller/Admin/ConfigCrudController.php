<?php

namespace App\Controller\Admin;

use App\Entity\Config;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Config::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            TextField::new('key'),
            TextField::new('value'),
        ];
    }
}
