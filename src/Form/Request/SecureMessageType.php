<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Model\DTO\SecureMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotNull;

class SecureMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('secondsLimit', IntegerType::class, [
                'constraints' => [
                    new NotNull(),
                    new NotEqualTo(['value' => 0]),
                ],
            ])
            ->add('requestsLimit', IntegerType::class, [
                'constraints' => [
                    new NotNull(),
                    new NotEqualTo(['value' => 0]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SecureMessage::class,
        ]);
    }
}
