<?php

declare(strict_types=1);

namespace App\Form\Request\Billing;

use App\Model\DTO\UserSubscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionGrantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('external_subscription_id', null, [
                'property_path' => 'subscriptionId',
            ])
            ->add('created')
            ->add('status')
            ->add('subscribed_at', null, [
                'property_path' => 'subscribedAt',
            ])
            ->add('items_limit', TextType::class, [
                'property_path' => 'itemsLimit',
            ])
            ->add('teams_limit', TextType::class, [
                'property_path' => 'teamsLimit',
            ])
            ->add('memory_limit', TextType::class, [
                'property_path' => 'memoryLimit',
            ])
            ->add('users_limit', TextType::class, [
                'property_path' => 'usersLimit',
            ])
            ->add('subscription_name', TextType::class, [
                'property_path' => 'subscriptionName',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserSubscription::class,
        ]);
    }
}