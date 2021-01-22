<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\User;
use App\Request\Item\CreateItemRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateItemRequestType extends AbstractType
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
            ->add('listId', EntityType::class, [
                'class' => AbstractDirectory::class,
                'choice_value' => 'id',
                'property_path' => 'list',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    NodeEnumType::TYPE_CRED,
                    NodeEnumType::TYPE_DOCUMENT,
                    NodeEnumType::TYPE_SYSTEM,
                ],
            ])
            ->add('secret', TextType::class)
            ->add('raws', TextType::class)
            ->add('meta', ItemMetaType::class)
            ->add('tags', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'data_class' => CreateItemRequest::class,
            'empty_data' => function (Options $options) {
                $user = $options['user'];

                return function (FormInterface $form) use ($user) {
                    return $form->isEmpty() && !$form->isRequired() ? null : new CreateItemRequest($user);
                };
            },
            'csrf_protection' => false,
        ]);
    }
}
