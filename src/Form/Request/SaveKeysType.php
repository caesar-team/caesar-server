<?php

declare(strict_types=1);

namespace App\Form\Request;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class SaveKeysType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('encryptedPrivateKey', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('publicKey', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}
