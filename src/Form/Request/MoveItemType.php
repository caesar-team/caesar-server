<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Directory;
use App\Entity\Item;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('listId', EntityType::class, [
                'class' => Directory::class,
                'choice_value' => 'id',
                'property_path' => 'parentList',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}
