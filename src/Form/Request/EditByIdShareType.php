<?php

declare(strict_types=1);

namespace App\Form\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class EditByIdShareType extends EditShareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', TextType::class, [
            'mapped' => false,
        ]);
    }
}
