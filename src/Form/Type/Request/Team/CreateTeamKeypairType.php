<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Team;

use App\Request\Team\CreateTeamKeypairRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateTeamKeypairType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('secret');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateTeamKeypairRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
