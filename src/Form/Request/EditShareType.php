<?php

declare(strict_types=1);

namespace App\Form\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EditShareType extends CreateShareType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('user');
        $builder->add('link', TextType::class);
    }
}
