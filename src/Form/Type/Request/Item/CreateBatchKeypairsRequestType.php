<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Request\Item\CreateBatchKeypairsRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateBatchKeypairsRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? null;
        $user = null;
        if ($data instanceof CreateBatchKeypairsRequest) {
            $user = $data->getUser();
        }

        $builder->add('items', CollectionType::class, [
            'entry_type' => CreateKeypairRequestType::class,
            'entry_options' => [
                'user' => $user,
            ],
            'allow_add' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateBatchKeypairsRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
