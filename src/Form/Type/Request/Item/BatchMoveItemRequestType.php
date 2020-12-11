<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Entity\Item;
use App\Request\Item\BatchMoveItemRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchMoveItemRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('itemId', EntityType::class, [
                'class' => Item::class,
                'choice_value' => 'id',
                'property_path' => 'item',
            ])
            ->add('secret', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchMoveItemRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
