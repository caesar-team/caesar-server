<?php

declare(strict_types=1);

namespace App\Form\Request\Srp;

use App\Entity\Srp;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginPrepareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('email', EmailType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('publicEphemeralValue', TextType::class, [
                'property_path' => 'publicClientEphemeralValue',
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Srp::class,
            'csrf_protection' => false,
        ]);
    }
}
