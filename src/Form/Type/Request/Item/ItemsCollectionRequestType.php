<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Entity\Item;
use App\Request\Item\ItemsCollectionRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemsCollectionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Item::class,
                    'choice_value' => 'id',
                ],
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemsCollectionRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
