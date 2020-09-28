<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email(),
                    new NotBlank(),
                ],
            ])
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
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => User::AVAILABLE_ROLES,
                'expanded' => true,
                'multiple' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'userFill']);
    }

    public function userFill(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getData();
        $user->setUsername($user->getEmail());
        $user->setEnabled(true);
        if ($user->hasRole(User::ROLE_READ_ONLY_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_CHANGE_PASSWORD);
        }
        if ($user->hasRole(User::ROLE_ANONYMOUS_USER)) {
            $user->setFlowStatus(User::FLOW_STATUS_FINISHED);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
