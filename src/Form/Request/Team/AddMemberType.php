<?php

declare(strict_types=1);

namespace App\Form\Request\Team;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AddMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
            ])
            ->add('userRole', ChoiceType::class, [
                'choices'  => UserTeam::ROLES,
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserTeam::class
        ]);
    }

}