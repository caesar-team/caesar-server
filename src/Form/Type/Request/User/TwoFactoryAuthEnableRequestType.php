<?php

declare(strict_types=1);

namespace App\Form\Type\Request\User;

use App\Request\User\TwoFactoryAuthEnableRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwoFactoryAuthEnableRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('secret')
            ->add('fingerprint')
            ->add('authCode')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TwoFactoryAuthEnableRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
