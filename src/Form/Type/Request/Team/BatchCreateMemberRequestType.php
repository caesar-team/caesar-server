<?php

declare(strict_types=1);

namespace App\Form\Type\Request\Team;

use App\Request\Team\BatchCreateMemberRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchCreateMemberRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? null;
        $team = null;
        if ($data instanceof BatchCreateMemberRequest) {
            $team = $data->getTeam();
        }

        $builder->add('members', CollectionType::class, [
            'entry_type' => CreateMemberRequestType::class,
            'entry_options' => [
                'team' => $team,
            ],
            'allow_add' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchCreateMemberRequest::class,
            'csrf_protection' => false,
        ]);
    }
}
