<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Item;
use App\Entity\User;
use App\Form\EventListener\InjectTagListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditItemType extends AbstractType
{
    private ?InjectTagListener $injectTagListener;

    private Security $security;

    public function __construct(?InjectTagListener $injectTagListener = null, Security $security)
    {
        $this->injectTagListener = $injectTagListener;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ownerId', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'choice_value' => 'id',
                'property_path' => 'owner',
            ])
            ->add('secret', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('tags', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
            ])
        ;

        if ($this->injectTagListener) {
            $builder->addEventSubscriber($this->injectTagListener);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
            'csrf_protection' => false,
        ]);
    }
}
