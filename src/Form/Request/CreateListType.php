<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Directory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('label', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('sort', IntegerType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Directory::class,
            'validation_groups' => [
                'Default', 'unique_label',
            ],
            'csrf_protection' => false,
        ]);
    }
}
