<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Srp;

use App\Request\Srp\RegistrationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('seed')
            ->add('verifier')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RegistrationRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
