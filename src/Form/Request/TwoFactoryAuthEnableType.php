<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Fingerprint;
use App\Entity\User;
use App\Security\Fingerprint\FingerprintStasher;
use App\Validator\Constraints\GoogleAuthenticatorCheckCode;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TwoFactoryAuthEnableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('secret', TextType::class, [
                'property_path' => 'googleAuthenticatorSecret',
            ])
            ->add('fingerprint', TextType::class, [
                'mapped' => false,
            ])
            ->add('authCode', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new GoogleAuthenticatorCheckCode(),
                ],
            ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'setFingerprint']);
    }

    public function setFingerprint(FormEvent $formEvent)
    {
        $form = $formEvent->getForm();
        $user = $formEvent->getData();

        $fingerprintString = $form->get('fingerprint')->getData();

        if (is_string($fingerprintString) && FingerprintStasher::isValidFingerprint($fingerprintString)) {
            $fingerprint = new Fingerprint($user, $fingerprintString);
            $user->addFingerprint($fingerprint);
        }
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
