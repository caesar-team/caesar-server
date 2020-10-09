<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Request\Item\KeypairFilterRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KeypairFilterRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    KeypairFilterRequest::TYPE_TEAM => KeypairFilterRequest::TYPE_TEAM,
                    KeypairFilterRequest::TYPE_PERSONAL => KeypairFilterRequest::TYPE_PERSONAL,
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => KeypairFilterRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
