<?php

declare(strict_types=1);

namespace App\Form\Query;

use App\Model\Query\UserQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class UserQueryType extends AbstractQueryType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Length(['min' => 3]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => UserQuery::class,
        ]);
    }
}
