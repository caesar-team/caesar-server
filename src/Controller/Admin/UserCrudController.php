<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class UserCrudController extends AbstractCrudController
{
    private const ROLES_AVAILABLE = [
        'domain admin' => User::ROLE_ADMIN,
        'super admin' => User::ROLE_SUPER_ADMIN,
        'manager' => User::ROLE_MANAGER,
        'read-only user' => User::ROLE_READ_ONLY_USER,
        'anonymous' => User::ROLE_ANONYMOUS_USER,
    ];

    private const FLOW_STATUS_AVAILABLE = [
        'finished' => User::FLOW_STATUS_FINISHED,
        'incomplete' => User::FLOW_STATUS_INCOMPLETE,
        'change_password' => User::FLOW_STATUS_CHANGE_PASSWORD,
    ];

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('username'),
            TextField::new('email'),
            ChoiceField::new('roles')
                ->setChoices(self::ROLES_AVAILABLE)
                ->setFormTypeOptions([
                    'required' => false,
                    'multiple' => true,
                ]),
            DateTimeField::new('lastLogin')
                ->setFormat('yyyy-MM-dd H:mm:s')
                ->hideOnForm(),
            TextField::new('domain'),
            TextField::new('flowStatus')
                ->hideOnForm(),
            BooleanField::new('enabled'),
            TextField::new('srp.id')
                ->setTemplatePath('admin/fields/_uuid.html.twig')
                ->hideOnForm(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $resetPassword = Action::new('resetPassword', 'Reset Password')
            ->linkToRoute('admin_user_reset_password', function (User $user) {
                return [
                    'user' => $user->getId(),
                ];
            })
            ->setTemplatePath('admin/actions/_confirm_action.html.twig')
        ;

        $reset2fa = Action::new('reset2fa', 'Reset 2FA')
            ->linkToRoute('admin_user_reset_2fa', function (User $user) {
                return [
                    'user' => $user->getId(),
                ];
            })
            ->setTemplatePath('admin/actions/_confirm_action.html.twig')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $resetPassword)
            ->add(Crud::PAGE_INDEX, $reset2fa)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Users')
            ->setSearchFields(['id', 'username', 'email'])
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('enabled'))
            ->add(ArrayFilter::new('roles')
                ->setChoices(self::ROLES_AVAILABLE)
                ->canSelectMultiple(true)
            )
            ->add(ChoiceFilter::new('flowStatus')
                ->setChoices(self::FLOW_STATUS_AVAILABLE)
                ->canSelectMultiple(true)
            )
        ;
    }
}
