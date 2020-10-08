<?php

declare(strict_types=1);

namespace App\Form\Type\Request\User;

use App\Request\User\CreateBatchInvitedUserRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateBatchInvitedUserRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('users', CollectionType::class, [
                'entry_type' => CreateInvitedUserRequestType::class,
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateBatchInvitedUserRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
