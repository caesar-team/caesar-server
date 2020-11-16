<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Request\Item\ShareBatchItemRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShareBatchItemRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? null;
        $item = null;
        if ($data instanceof ShareBatchItemRequest) {
            $item = $data->getItem();
        }

        $builder
            ->add('users', CollectionType::class, [
                'entry_type' => ShareItemRequestType::class,
                'entry_options' => [
                    'item' => $item,
                ],
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShareBatchItemRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
