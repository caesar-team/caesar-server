<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Request\Item\ItemMetaRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attachmentsCount', IntegerType::class, [
                'required' => false,
            ])
            ->add('website', TextType::class, [
                'required' => false,
            ])
            ->add('title', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemMetaRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
