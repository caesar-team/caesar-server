<?php

declare(strict_types=1);

namespace App\Form\Request\Srp;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ])
            ->add('seed', TextType::class, [
                'property_path' => 'srp.seed',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('verifier', TextType::class, [
                'property_path' => 'srp.verifier',
                'constraints' => [
                    new NotBlank(),
                ],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'fillData']);
    }

    public function fillData(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getData();

        $user->setUsername($user->getEmail());
        $user->setPlainPassword(uniqid());
        $user->setEnabled(true);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
