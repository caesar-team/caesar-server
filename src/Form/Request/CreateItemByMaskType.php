<?php

declare(strict_types=1);

namespace App\Form\Request;


use App\Entity\Item;
use App\Entity\ItemMask;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateItemByMaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('secret', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('originalItem', EntityType::class, [
                'constraints' => [new NotBlank()],
                'class' => Item::class,
            ])
            ->add('recipient', EntityType::class, [
                'constraints' => [new NotBlank()],
                'class' => User::class,
            ])
            ->add('access', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => ItemMask::class,
        ]);
    }
}