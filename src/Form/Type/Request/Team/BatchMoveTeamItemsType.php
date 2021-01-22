<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Team;

use App\Request\Team\BatchMoveTeamItemsRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchMoveTeamItemsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? null;
        $team = null;
        $directory = null;
        if ($data instanceof BatchMoveTeamItemsRequest) {
            $team = $data->getTeam();
            $directory = $data->getDirectory();
        }

        $builder
            ->add('items', CollectionType::class, [
                'entry_type' => MoveTeamItemType::class,
                'entry_options' => ['team' => $team, 'directory' => $directory],
                'property_path' => 'moveItemRequests',
                'allow_add' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchMoveTeamItemsRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
