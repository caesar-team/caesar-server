<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Team;

use App\Entity\User;
use App\Request\Team\CreateMemberRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateMemberWithUserRequestType extends CreateMemberRequestType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('userId', EntityType::class, [
            'property_path' => 'user',
            'class' => User::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'team' => null,
            'empty_data' => function (Options $options) {
                $team = $options['team'];

                return function (FormInterface $form) use ($team) {
                    return $form->isEmpty() && !$form->isRequired() ? null : new CreateMemberRequest($team);
                };
            },
        ]);
    }
}
