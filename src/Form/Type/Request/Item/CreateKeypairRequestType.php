<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Request\Item\CreateKeypairRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateKeypairRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ownerId', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'choice_value' => 'id',
                'property_path' => 'owner',
            ])
            ->add('teamId', EntityType::class, [
                'class' => Team::class,
                'choice_value' => 'id',
                'property_path' => 'team',
            ])
            ->add('secret', TextType::class)
            ->add('relatedItemId', EntityType::class, [
                'class' => Item::class,
                'choice_value' => 'id',
                'property_path' => 'relatedItem',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'data_class' => CreateKeypairRequest::class,
            'empty_data' => function (Options $options) {
                $user = $options['user'];

                return function (FormInterface $form) use ($user) {
                    return $form->isEmpty() && !$form->isRequired() ? null : new CreateKeypairRequest($user);
                };
            },
            'csrf_protection' => false,
        ]);
    }
}
