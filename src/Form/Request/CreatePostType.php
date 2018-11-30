<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreatePostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('listId', EntityType::class, [
                'class' => Directory::class,
                'choice_value' => 'id',
                'property_path' => 'parentList',
            ])
            ->add('type', ChoiceType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'choices' => [
                    NodeEnumType::TYPE_CRED,
                    NodeEnumType::TYPE_DOCUMENT,
                ],
            ])
            ->add('secret', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('favorite', CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
