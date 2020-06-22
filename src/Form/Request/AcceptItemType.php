<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class AcceptItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', TextType::class)
            ->add('status', TextType::class, [
                'constraints' => [
                    new Choice([
                        'choices' => [
                            Item::STATUS_FINISHED => Item::STATUS_FINISHED,
                            Item::STATUS_OFFERED => Item::STATUS_OFFERED,
                        ],
                    ]),
                ],
                'empty_data' => Item::STATUS_FINISHED,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }
}
