<?php

declare(strict_types=1);

namespace App\Form\Request\Message;

use App\Mailer\MailRegistry;
use App\Model\Request\Message\MessageRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('template', ChoiceType::class, [
            'choices' => [
                MailRegistry::MESSAGE_TEMPLATES
                ],
            ])
            ->add('recipients', CollectionType::class, [
                'allow_add' => true,
            ])
            ->add('content')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessageRequest::class,
        ]);
    }
}