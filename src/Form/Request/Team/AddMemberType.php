<?php

declare(strict_types=1);

namespace App\Form\Request\Team;

use App\Entity\UserTeam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AddMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userRole', ChoiceType::class, [
                'choices' => UserTeam::ROLES,
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
