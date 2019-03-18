<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Model\Request\ItemMaskCollctionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ItemMasksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('masks', CollectionType::class, [
            'constraints' => [
                new NotBlank(),
            ],
            'entry_type' => ItemMaskType::class,
            'allow_add' => true,
            'by_reference' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemMaskCollctionRequest::class
        ]);
    }

}