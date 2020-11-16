<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Key;

use App\Request\Key\PublicKeysRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PublicKeysRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('emails', CollectionType::class, [
            'allow_add' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PublicKeysRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
