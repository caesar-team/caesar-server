<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Item;

use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Request\Item\MovePersonalItemRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovePersonalItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('secret', TextType::class, [
                'required' => false,
            ])
        ;

        if (null === $options['directory']) {
            $builder->add('listId', EntityType::class, [
                'class' => AbstractDirectory::class,
                'choice_value' => 'id',
                'property_path' => 'directory',
            ]);
        } else {
            $builder->add('itemId', EntityType::class, [
                'class' => Item::class,
                'choice_value' => 'id',
                'property_path' => 'item',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'directory' => null,
            'empty_data' => function (Options $options) {
                return function (FormInterface $form) use ($options) {
                    if ($form->isEmpty() && !$form->isRequired()) {
                        return null;
                    }

                    $request = new MovePersonalItemRequest($options['user']);
                    $request->setDirectory($options['directory']);

                    return $request;
                };
            },
            'data_class' => MovePersonalItemRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
