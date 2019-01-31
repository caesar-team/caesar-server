<?php

declare(strict_types=1);

namespace App\Form\Request\Invite;

use App\DBAL\Types\Enum\AccessEnumType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class InviteType extends SecretType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('access', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'choices' => AccessEnumType::getValues(),
            ]);
    }
}
