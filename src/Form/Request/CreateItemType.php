<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Form\EventListener\InjectTagListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateItemType extends AbstractType
{
    private ?InjectTagListener $injectTagListener;

    public function __construct(?InjectTagListener $injectTagListener = null)
    {
        $this->injectTagListener = $injectTagListener;
    }

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
                    NodeEnumType::TYPE_SYSTEM,
                ],
            ])
            ->add('secret', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('favorite', CheckboxType::class)
            ->add('tags', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
            ])
            ->add('relatedItemId', EntityType::class, [
                'class' => Item::class,
                'choice_value' => 'id',
                'property_path' => 'relatedItem',
                'constraints' => [
                    new NotBlank(['groups' => ['personal']]),
                ],
            ])
        ;

        if ($this->injectTagListener) {
            $builder->addEventSubscriber($this->injectTagListener);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Item::class,
            'csrf_protection' => false,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                $item = $form->getData();
                if (!$item instanceof Item) {
                    return $groups;
                }

                if (NodeEnumType::TYPE_SYSTEM === $item->getType()
                    && $item->getParentList()
                    && null === $item->getParentList()->getTeam()
                ) {
                    $groups[] = 'personal';
                }

                return $groups;
            },
        ]);
    }
}
