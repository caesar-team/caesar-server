<?php

declare(strict_types=1);

namespace App\Form\Request\Invite;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class InviteUpdateRequestType extends AbstractType
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

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Item::class,
            'csrf_protection' => false,
        ]);
    }
}
