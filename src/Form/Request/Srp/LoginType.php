<?php

declare(strict_types=1);

namespace App\Form\Request\Srp;

use App\Entity\User;
use App\Model\Request\LoginRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('email', EntityType::class, [
                'property_path' => 'user',
                'choice_value' => 'email',
                'class' => User::class,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('matcher', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => LoginRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
