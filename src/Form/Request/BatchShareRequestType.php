<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Form\Request\Team\BatchTeamsItemsCollectionRequestType;
use App\Model\Request\BatchShareRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchShareRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('personals', CollectionType::class, [
                'entry_type' => BatchChildItemsRequestType::class,
                'allow_add' => true,
            ])
            ->add('teams', CollectionType::class, [
                'entry_type' => BatchTeamsItemsCollectionRequestType::class,
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchShareRequest::class
        ]);
    }
}