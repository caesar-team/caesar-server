<?php

declare(strict_types=1);

namespace App\Form\Type\Admin;

use App\Entity\User;
use App\Entity\UserTeam;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserTeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('user')
                        ->andWhere('LOWER(user.roles) NOT LIKE :role')
                        ->setParameter('role', '%'.mb_strtolower(User::ROLE_ANONYMOUS_USER).'%')
                    ;
                },
            ])
            ->add('userRole', ChoiceType::class, [
                'choices' => [
                    UserTeam::USER_ROLE_MEMBER => 'member',
                    UserTeam::USER_ROLE_ADMIN => 'admin',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserTeam::class,
            'csrf_protection' => false,
        ]);
    }
}
