<?php

declare(strict_types=1);

namespace App\Form\Request;

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

class CreateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('login', TextType::class)
            ->add('plainPassword', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('encryptedPrivateKey', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('publicKey', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'userFill']);
    }

    public function userFill(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getData();
        $user->setUsername($user->getEmail()?:$user->getLogin()());
        $user->setRequireMasterRefresh(true);
        $user->setEnabled(true);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
