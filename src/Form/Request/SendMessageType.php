<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\User;
use App\Model\Request\SendMessageRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

final class SendMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
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
            ->add('token', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('url', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Url(),
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => SendMessageRequest::class,
        ]);
    }
}
