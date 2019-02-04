<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Model\Request\ShareCollectionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BatchCreateShareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('shares', CollectionType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'allow_add' => true,
                'entry_type' => CreateShareType::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => ShareCollectionRequest::class,
        ]);
    }
}
