<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Item;
use App\Form\Request\Invite\CreateChildItemType;
use App\Model\Request\BatchItemCollectionRequest;
use App\Model\Request\ItemCollectionRequest;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Tests\Functional\Form\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BatchChildItemsRequestType extends AbstractType
{
    /**
     * @var UserRepository
     */
    protected $userRepository;
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    public function __construct(UserRepository $userRepository, ItemRepository $itemRepository)
    {
        $this->userRepository = $userRepository;
        $this->itemRepository = $itemRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('originalItem', TextType::class)
            ->add('items', CollectionType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
                'allow_add' => true,
                'entry_type' => CreateChildItemType::class,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'validateChildItems']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchItemCollectionRequest::class
        ]);
    }

    /**
     * @param FormEvent $event
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validateChildItems(FormEvent $event)
    {
        /** @var ItemCollectionRequest $request */
        $request = $event->getData();
        $form = $event->getForm();

        $parentItem = $this->itemRepository->find($request->getOriginalItem());
        if (!$parentItem instanceof Item) {
            $form->addError(new FormError('Parent item not found'));
        }
        foreach ($request->getItems() as $invite) {
            foreach ($parentItem->getSharedItems() as $sharedItem) {
                $owner = $this->userRepository->getByItem($sharedItem);

                if ($invite->getUser() === $owner) {
                    $form->addError(new FormError('item.invite.user.already_invited'));

                    return;
                }
            }
        }
    }
}