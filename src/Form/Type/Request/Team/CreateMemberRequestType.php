<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Team;

use App\Entity\UserTeam;
use App\Request\Team\CreateMemberRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateMemberRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userRole', ChoiceType::class, [
                'choices' => UserTeam::ROLES,
            ])
            ->add('secret', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateMemberRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
