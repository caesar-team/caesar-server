<?php

namespace App\Controller\Admin;

use App\Entity\UserTeam;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserTeamCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserTeam::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('user')
                ->setFormTypeOption('disabled', true),
            DateTimeField::new('createdAt')
                ->setFormat('yyyy-MM-dd H:mm:s')
                ->hideOnForm(),
            AssociationField::new('team')
                ->setFormTypeOption('disabled', true),
            TextField::new('userRole')
                ->setFormType(ChoiceType::class)
                ->setFormTypeOptions([
                    'choices' => [
                        'member' => UserTeam::USER_ROLE_MEMBER,
                        'admin' => UserTeam::USER_ROLE_ADMIN,
                    ],
                ]),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, 'User teams');
    }
}
