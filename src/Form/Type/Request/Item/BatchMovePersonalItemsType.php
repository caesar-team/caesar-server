<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Request\Item\BatchMovePersonalItemsRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchMovePersonalItemsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? null;
        $user = null;
        $directory = null;
        if ($data instanceof BatchMovePersonalItemsRequest) {
            $user = $data->getUser();
            $directory = $data->getDirectory();
        }

        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => MovePersonalItemType::class,
                'entry_options' => ['user' => $user, 'directory' => $directory],
                'property_path' => 'moveItemRequests',
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchMovePersonalItemsRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
