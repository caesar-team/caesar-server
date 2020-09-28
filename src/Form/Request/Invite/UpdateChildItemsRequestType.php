<?php

declare(strict_types=1);

namespace App\Form\Request\Invite;

use App\Entity\Item;
use App\Model\Request\ItemCollectionRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateChildItemsRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('originalItem', EntityType::class, [
                'class' => Item::class,
                'empty_data' => $builder->getData() ? $builder->getData()->getOriginalItem()->getId()->toString() : null,
            ])
            ->add('items', CollectionType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'allow_add' => true,
                'entry_type' => SecretType::class,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemCollectionRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
