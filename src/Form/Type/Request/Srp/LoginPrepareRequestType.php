<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Srp;

use App\Entity\User;
use App\Request\Srp\LoginPrepareRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginPrepareRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EntityType::class, [
                'property_path' => 'user',
                'choice_value' => 'email',
                'class' => User::class,
                'required' => false,
                'invalid_message' => 'app.exception.user_not_found',
            ])
            ->add('publicEphemeralValue');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LoginPrepareRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
