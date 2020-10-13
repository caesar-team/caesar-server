<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Entity\User;
use App\Request\Item\ShareItemRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShareItemRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userId', EntityType::class, [
                'class' => User::class,
                'choice_value' => 'id',
                'property_path' => 'user',
            ])
            ->add('secret', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShareItemRequest::class,
            'csrf_protection' => false,
            'item' => null,
            'empty_data' => function (Options $options) {
                $item = $options['item'];

                return function (FormInterface $form) use ($item) {
                    return $form->isEmpty() && !$form->isRequired() ? null : new ShareItemRequest($item);
                };
            },
        ]);
    }
}
