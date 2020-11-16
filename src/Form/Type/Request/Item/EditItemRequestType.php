<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Entity\User;
use App\Request\Item\EditItemRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditItemRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ownerId', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'choice_value' => 'id',
                'property_path' => 'owner',
            ])
            ->add('title', TextType::class)
            ->add('secret', TextType::class)
            ->add('meta', ItemMetaType::class)
            ->add('tags', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EditItemRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
