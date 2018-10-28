<?php

declare(strict_types=1);

namespace App\Form\Query;

use App\Entity\Directory;
use App\Model\Query\PostListQuery;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PostListQueryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('listId', EntityType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'class' => Directory::class,
                'choice_value' => 'id',
                'property_path' => 'list',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => PostListQuery::class,
        ]);
    }
}
