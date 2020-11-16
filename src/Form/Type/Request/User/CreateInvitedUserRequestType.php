<?php

declare(strict_types=1);

namespace App\Form\Type\Request\User;

use App\Entity\User;
use App\Request\User\CreateInvitedUserRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateInvitedUserRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('plainPassword', TextType::class)
            ->add('encryptedPrivateKey', TextType::class)
            ->add('publicKey', TextType::class)
            ->add('seed', TextType::class)
            ->add('verifier', TextType::class)
            ->add('domainRoles', ChoiceType::class, [
                'choices' => User::AVAILABLE_ROLES,
                'expanded' => true,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateInvitedUserRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
