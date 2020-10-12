<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Team;

use App\Request\Team\CreateVaultRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateVaultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team', CreateTeamRequestType::class)
            ->add('keypair', CreateTeamKeypairType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CreateVaultRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
