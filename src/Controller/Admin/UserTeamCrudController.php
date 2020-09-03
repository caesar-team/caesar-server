<?php

namespace App\Controller\Admin;

use App\Entity\UserTeam;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserTeamCrudController extends AbstractCrudController
{
    private const AVAILABLE_USER_ROLE = [
        'member' => UserTeam::USER_ROLE_MEMBER,
        'admin' => UserTeam::USER_ROLE_ADMIN,
    ];

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
                    'choices' => self::AVAILABLE_USER_ROLE,
                ]),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setPageTitle(Crud::PAGE_INDEX, 'Users teams');
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
            ->add(EntityFilter::new('user')->setFormTypeOptions($multipleOptions))
            ->add(EntityFilter::new('team')->setFormTypeOptions($multipleOptions))
            ->add(ChoiceFilter::new('userRole')->setChoices(self::AVAILABLE_USER_ROLE))
        ;
    }
}
