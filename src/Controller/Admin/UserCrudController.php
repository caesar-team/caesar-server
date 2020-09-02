<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
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
        return $crud->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        //@todo update after merge https://github.com/EasyCorp/EasyAdminBundle/pull/3725/files
//        $rolesFilter = ArrayFilter::new('roles')
//            ->setChoices(self::ROLES_AVAILABLE)
//            ->canSelectMultiple(true)
//        ;

        $rolesFilter = ChoiceFilter::new('roles')
            ->setChoices(self::ROLES_AVAILABLE)
            ->canSelectMultiple(true)
        ;

        $rolesFilter
            ->getAsDto()
            ->setApplyCallable(static function (QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto) {
                $alias = $filterDataDto->getEntityAlias();
                $property = $filterDataDto->getProperty();
                $comparison = $filterDataDto->getComparison();
                $parameterName = $filterDataDto->getParameterName();
                $value = $filterDataDto->getValue();
                $isMultiple = $filterDataDto->getFormTypeOption('value_type_options.multiple');

                if ('NOT IN' === $comparison) {
                    $comparison = 'NOT LIKE';
                } else {
                    $comparison = 'LIKE';
                }

                if (null === $value || ($isMultiple && 0 === \count($value))) {
                    $queryBuilder->andWhere(sprintf('%s.%s %s', $alias, $property, $comparison));
                } else {
                    $orX = new Orx();
                    if ($isMultiple && is_array($value)) {
                        foreach ($value as $i => $val) {
                            $parameterNameVal = sprintf('%s_%s', $parameterName, $i);
                            $orX->add(sprintf('%s.%s %s :%s', $alias, $property, $comparison, $parameterNameVal));
                            $queryBuilder->setParameter($parameterNameVal, '%'.$val.'%');
                        }
                    } else {
                        $orX->add(sprintf('%s.%s %s :%s', $alias, $property, $comparison, $parameterName));
                        $queryBuilder->setParameter($parameterName, '%'.$value.'%');
                    }

                    $queryBuilder->andWhere($orX);
                }
            })
        ;

        return $filters
            ->add(BooleanFilter::new('enabled'))
            ->add($rolesFilter)
            ->add(ChoiceFilter::new('flowStatus')
                ->setChoices(self::FLOW_STATUS_AVAILABLE)
                ->canSelectMultiple(true)
            )
        ;
    }
}
