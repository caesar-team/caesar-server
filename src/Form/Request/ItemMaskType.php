<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\ItemMask;
use App\Model\Request\ItemMaskRequest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ItemMaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('itemMask', EntityType::class, [
            'constraints' => [new NotBlank()],
            'class' => ItemMask::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ItemMaskRequest::class
        ]);
    }

}