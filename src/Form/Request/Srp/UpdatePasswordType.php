<?php

declare(strict_types=1);

namespace App\Form\Request\Srp;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdatePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('seed', TextType::class, [
                'property_path' => 'srp.seed',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('verifier', TextType::class, [
                'property_path' => 'srp.verifier',
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
