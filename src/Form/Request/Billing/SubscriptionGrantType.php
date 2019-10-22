<?php

declare(strict_types=1);


namespace App\Form\Request\Billing;


use App\Model\DTO\UserSubscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionGrantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status')
            ->add('created')
            ->add('externalSubscriptionId')
            ->add('user', UserType::class)
            ->add('id')
            ->add('user')
            ->add('subscription')
            ->add('externalSubscriptionId')
            ->add('itemsLimit')
            ->add('created')
            ->add('status')
            ->add('subscribedAt')
            ->add('metaData')
            ->add('teamsLimit')
            ->add('memoryLimit')
            ->add('subscriptionName')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserSubscription::class,
        ]);
    }
}