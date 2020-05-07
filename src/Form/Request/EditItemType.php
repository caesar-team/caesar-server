<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Item;
use App\Form\EventListener\InjectTagListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditItemType extends AbstractType
{
    /**
     * @var InjectTagListener
     */
    private $injectTagListener;

    public function __construct(?InjectTagListener $injectTagListener = null)
    {
        $this->injectTagListener = $injectTagListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
        ]);
    }
}
