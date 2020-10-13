<?php

declare(strict_types=1);

namespace App\Form\Type\Request\User;

use App\Request\User\SaveKeysRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaveKeysRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('encryptedPrivateKey', TextType::class)
            ->add('publicKey', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SaveKeysRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
