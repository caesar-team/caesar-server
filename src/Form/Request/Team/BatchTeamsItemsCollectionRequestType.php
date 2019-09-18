<?php

declare(strict_types=1);

namespace App\Form\Request\Team;

use App\Entity\Team;
use App\Form\Request\BatchChildItemsRequestType;
use App\Model\Request\Team\BatchTeamsItemsCollectionRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchTeamsItemsCollectionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'property_path' => 'id',
            ])
            ->add('shares', CollectionType::class, [
                'entry_type' => BatchChildItemsRequestType::class,
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchTeamsItemsCollectionRequest::class,
        ]);
    }

}