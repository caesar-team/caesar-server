<?php

declare(strict_types=1);


namespace App\Form\Request;


use App\Model\Request\EditItemRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditItemRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('originalItem', EditOriginalItemType::class)
            ->add('item', EditItemType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => EditItemRequest::class,
        ]);
    }

}