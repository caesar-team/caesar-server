<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Share;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class CreateShareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('user', ShareUserType::class, [
                'mapped' => false,
            ])
            ->add('sharedItems', CollectionType::class, [
                'entry_type' => ShareItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'constraints' => [
                    new Count(['min' => 1]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Share::class,
        ]);
    }
}
