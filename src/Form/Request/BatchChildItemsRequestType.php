<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Item;
use App\Form\Request\Invite\CreateChildItemType;
use App\Model\Request\BatchItemCollectionRequest;
use App\Repository\ItemRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class BatchChildItemsRequestType extends AbstractType
{
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('originalItem', EntityType::class, [
                'class' => Item::class,
            ])
            ->add('items', CollectionType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Valid(),
                ],
                'allow_add' => true,
                'entry_type' => CreateChildItemType::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchItemCollectionRequest::class,
            'er'
        ]);
    }
}