<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Request\Item\BatchMoveItemsCollectionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchMoveItemsCollectionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => BatchMoveItemRequestType::class,
                'property_path' => 'moveItemRequests',
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchMoveItemsCollectionRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
