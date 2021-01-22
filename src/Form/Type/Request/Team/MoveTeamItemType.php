<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Team;

use App\Entity\Directory\AbstractDirectory;
use App\Request\Team\MoveTeamItemRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveTeamItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('listId', EntityType::class, [
                'class' => AbstractDirectory::class,
                'choice_value' => 'id',
                'property_path' => 'directory',
            ])
            ->add('secret', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'team' => null,
            'directory' => null,
            'empty_data' => function (Options $options) {
                return function (FormInterface $form) use ($options) {
                    if ($form->isEmpty() && !$form->isRequired()) {
                        return null;
                    }

                    $request = new MoveTeamItemRequest($options['team']);
                    $request->setDirectory($options['directory']);

                    return $request;
                };
            },
            'data_class' => MoveTeamItemRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
