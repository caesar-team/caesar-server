<?php

declare(strict_types=1);

namespace App\Form\Request\Invite;

use App\Entity\Item;
use App\Entity\User;
use App\Model\Request\ChildItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class SecretType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('name')
            ->add('userId', EntityType::class, [
                'property_path' => 'user',
                'class' => User::class,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('secret', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('cause', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Choice([
                        'choices' => [
                            Item::CAUSE_INVITE => Item::CAUSE_INVITE,
                            Item::CAUSE_SHARE => Item::CAUSE_SHARE,
                        ],
                    ]),
                ],
                'choices' => [
                    Item::CAUSE_INVITE => Item::CAUSE_INVITE,
                    Item::CAUSE_SHARE => Item::CAUSE_SHARE,
                ],
                'empty_data' => Item::CAUSE_INVITE
            ])
            ->add('link', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => ChildItem::class,
        ]);
    }
}
