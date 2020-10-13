<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Entity\Directory;
use App\Request\Item\MoveItemRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveItemRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('listId', EntityType::class, [
                'class' => Directory::class,
                'choice_value' => 'id',
                'property_path' => 'list',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MoveItemRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
