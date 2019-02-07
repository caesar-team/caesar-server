<?php

declare(strict_types=1);

namespace App\Form\Request;

use App\Entity\Item;
use App\Model\Request\LinkCreateRequest;
use App\Repository\ItemRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LinkCreateRequestType extends AbstractType implements DataTransformerInterface
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
            ->add('itemId', TextType::class, [
                'property_path' => 'item',
            ])
            ->add('publicKey', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('encryptedPrivateKey', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('secret', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                ],
            ]);

        $builder->get('itemId')->addModelTransformer($this);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'checkExistLink']);
    }

    public function checkExistLink(FormEvent $event)
    {
        /** @var LinkCreateRequest $linkRequest */
        $linkRequest = $event->getData();
        $form = $event->getForm();

        $item = $linkRequest->getItem();

        if (null !== $item && null !== $item->getLink()) {
            $form->get('itemId')->addError(new FormError('Item already contains link'));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => LinkCreateRequest::class,
        ]);
    }

    /**
     * @param Item|null $value
     *
     * @return null|string
     */
    public function transform($value)
    {
        if ($value instanceof Item) {
            return $value->getId()->toString();
        }

        return null;
    }

    /**
     * @param string|null $value
     *
     * @return null|Item
     */
    public function reverseTransform($value)
    {
        if (Uuid::isValid($value)) {
            /** @var Item $item */
            $item = $this->itemRepository->findOneBy(['id' => $value]);

            if ($item instanceof Item) {
                return $item;
            }
        }

        throw  new TransformationFailedException('No such item');
    }
}
